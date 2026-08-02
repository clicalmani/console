<?php

/**
 * Messenger Worker
 * Usage : php worker.php --transport=elegant
 */

// ── Lock: ensure only one worker instance runs at a time ───────────────────
$lockFile = sys_get_temp_dir() . '/tonka_messenger_worker.lock';
$fp = fopen($lockFile, 'w+');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    logger()->error("A worker is already running.");
    exit(1);
}

// ── CLI Options ─────────────────────────────────────────────────────────────
$options       = getopt("", ["transport:"]);
$transportName = ($options['transport'] ?? 'elegant') === 'elegant'
    ? 'messenger.transport.elegant'
    : ($options['transport'] ?? 'messenger.transport.elegant');

// ── Bootstrap ────────────────────────────────────────────────────────────────
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\EventSubscribers\MessengerEventSubscriber;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

$root = dirname(__DIR__, 6);
include_once $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->config->set('database', require_once config_path('/database.php'));
$app->boot();

$requestedTransports = $options['transport'] ?? 'elegant';
$transportKeys = array_map('trim', explode(',', $requestedTransports));

// ── Dynamic Receiver Construction ─────────────────────────────────────
$receivers = [];

foreach ($transportKeys as $key) {
    $serviceIdentifier = 'messenger.transport.' . $key;

    if (container()->has($serviceIdentifier)) {
        // Symfony expects an associative array: ['receiver_name' => $transport_instance]
        $receivers[$key] = container()->get($serviceIdentifier);
    } else {
        logger()->error("The requested transport '{$key}' (Service: {$serviceIdentifier}) is not configured in the container.");
        exit(1);
    }
}

// ── Services from container ─────────────────────────────────────────────
$failureTransports = container()->get('messenger.failure_transports');
$bus               = container()->get('messenger');
$maxRetries        = config('messenger.retries', 3);

// ── Event Dispatcher ──────────────────────────────────────────────────────────
$dispatcher = new EventDispatcher();

// Graceful stop on SIGTERM / SIGINT — class name changed in Symfony 6.3
if (class_exists(\Symfony\Component\Messenger\EventListener\StopWorkerOnSignalsListener::class)) {
    // Symfony >= 6.3
    $dispatcher->addSubscriber(new \Symfony\Component\Messenger\EventListener\StopWorkerOnSignalsListener());
} elseif (class_exists(\Symfony\Component\Messenger\EventListener\StopWorkerOnSigtermSigintListener::class)) {
    // Symfony < 6.3
    $dispatcher->addSubscriber(new \Symfony\Component\Messenger\EventListener\StopWorkerOnSigtermSigintListener());
}

$retryStrategyLocator = new \Clicalmani\Foundation\Messenger\RetryStrategyManager(config('messenger.retry_strategy'));
$retryTransportsLocator = new class implements \Psr\Container\ContainerInterface {
    public function has(string $id): bool { return true; }
    public function get(string $id): mixed { return container()->get('messenger.transport.' . $id); }
};

$dispatcher->addSubscriber(
    new SendFailedMessageForRetryListener(
        $retryTransportsLocator,
        $retryStrategyLocator,
        null,
        $dispatcher
    )
);

$dispatcher->addListener(
    WorkerMessageFailedEvent::class,
    function (WorkerMessageFailedEvent $event) use ($maxRetries) {
        $envelope  = $event->getEnvelope();
        $throwable = $event->getThrowable();

        // 💡 Find which transport received the message ('elegant', 'rabbitmq', 'redis'...)
        $receiverName = $event->getReceiverName();

        // 💡 IF IT IS NOT ELEGANT, EXIT!
        // Let Symfony's native listener handle it in a non-blocking way.
        if ($receiverName !== 'elegant') {
            return; 
        }

        // Dynamically fetch the transport instance from Tonka's container
        $currentTransport = container()->get('messenger.transport.' . $receiverName);

        // Extract the original exception from HandlerFailedException
        $originalException = $throwable;
        if ($throwable instanceof \Symfony\Component\Messenger\Exception\HandlerFailedException) {
            if (method_exists($throwable, 'getWrappedExceptions')) {
                $nested = $throwable->getWrappedExceptions();
            } elseif (method_exists($throwable, 'getNestedExceptions')) {
                $nested = $throwable->getNestedExceptions();
            } else {
                $nested = [];
            }
            $originalException = !empty($nested)
                ? array_values($nested)[0]
                : ($throwable->getPrevious() ?? $throwable);
        }

        /** @var RedeliveryStamp|null $redelivery */
        $redelivery = $envelope->last(RedeliveryStamp::class);
        $retryCount = $redelivery ? $redelivery->getRetryCount() : 0;

        // Always build the envelope with the error details
        $envelopeWithError = $envelope->with(new ErrorDetailsStamp(
            $originalException::class,
            $originalException->getCode(),
            $originalException->getMessage(),
        ));

        if ($retryCount < $maxRetries) {
            // ── Retry ────────────────────────────────────────────────────────
            $nextRetry = $retryCount + 1;
            $delayMs   = 1000 * (2 ** $retryCount); // 1s → 2s → 4s

            $retryEnvelope = $envelopeWithError
                ->withoutAll(RedeliveryStamp::class)
                ->with(new RedeliveryStamp($nextRetry));

            // ← Signals reject() that a retry is currently in progress
            // by modifying the envelope that the Worker will pass to reject()
            $event->addStamps(new \Clicalmani\Foundation\Messenger\Stamp\RetryingStamp($nextRetry));

            // Marks the event as "handled" — prevents Messenger from 
            // re-dispatching the message immediately and creating duplicates
            $event->setForRetry();

            usleep($delayMs * 1000);
            $currentTransport->send($retryEnvelope);

            logger()->warning("⚠️  Retry {$nextRetry}/{$maxRetries}", [
                'class'    => get_class($envelope->getMessage()),
                'error'    => $originalException->getMessage(),
                'delay_ms' => $delayMs,
            ]);

        } else {
            // 1. 💡 Attach the error stamp directly to the event.
            // This is the stamp that Symfony's native listener looks for.
            $event->addStamps(new \Symfony\Component\Messenger\Stamp\ErrorDetailsStamp(
                $originalException::class,
                $originalException->getCode(),
                $originalException->getMessage()
            ));

            logger()->warning("🔴 Quarantine after {$maxRetries} attempts", [
                'class' => get_class($envelope->getMessage()),
                'error' => $originalException->getMessage(),
            ]);
        }
    },
    100
);

// Sends permanently failed messages to the failure transport
$dispatcher->addSubscriber(
    new SendFailedMessageToFailureTransportListener(
        $failureTransports  // ServiceLocator { 'elegant' => failed_transport }
    )
);

// Custom (logs + alerts)
$subscribersDir = root_path('app/EventSubscribers'); 
$baseNamespace  = 'App\\EventSubscribers';

$subscribers = \Clicalmani\Foundation\Messenger\SubscriberDiscovery::discover($subscribersDir, $baseNamespace);

foreach ($subscribers as $subscriber) {
    $dispatcher->addSubscriber($subscriber);
}

// ── Worker ───────────────────────────────────────────────────────────────────
$worker = new Worker($receivers, $bus, $dispatcher);

logger()->info("Worker started — waiting for messages...", [
    'transport' => $transportName,
    'pid'       => getmypid(),
]);

$worker->run([], function () {
    // Restart signal: create the temp/restart_worker.txt file for a graceful stop
    if (file_exists(root_path('temp/restart_worker.txt'))) {
        unlink(root_path('temp/restart_worker.txt'));
        logger()->info("Restart signal received — stopping the worker.");
        return false;
    }
    return true;
});

// ── Release the lock on shutdown ─────────────────────────────────────────────
flock($fp, LOCK_UN);
fclose($fp);