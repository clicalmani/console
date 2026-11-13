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
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
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

$retryStrategyLocator = new \Clicalmani\Core\Messenger\RetryStrategyManager(config('messenger.retry_strategy'));
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

// Sends permanently failed messages to the failure transport
$dispatcher->addSubscriber(
    new SendFailedMessageToFailureTransportListener(
        $failureTransports  // ServiceLocator { 'elegant' => failed_transport }
    )
);

$dispatcher->addSubscriber(new AddErrorDetailsStampListener());

// Custom (logs + alerts)
$subscribersDir = root_path('app/EventSubscribers'); 
$baseNamespace  = 'App\\EventSubscribers';

$subscribers = \Clicalmani\Core\Messenger\SubscriberDiscovery::discover($subscribersDir, $baseNamespace);

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