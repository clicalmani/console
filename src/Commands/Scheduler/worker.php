<?php

$lockFile = sys_get_temp_dir() . '/tonka_scheduler_worker.lock';
$fp = fopen($lockFile, 'w+');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    logger()->error("A scheduler worker is already running.");
    exit(1);
}

$root = dirname(__DIR__, 6);
include_once $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->config->set('database', require_once config_path('/database.php'));
$app->boot();

// ── Schedule — retrieve all tasks ───────────────────────────────────────────
$schedule = container()->get('scheduler.main');
$bus      = container()->get('messenger');

// ── SchedulerTransport — generates messages based on the cron expression ───
// This component "ticks" and determines which messages to send depending on the time
$scheduleProvider = new class($schedule) implements \Symfony\Component\Scheduler\ScheduleProviderInterface {
    public function __construct(private \Symfony\Component\Scheduler\Schedule $schedule) {}
    
    public function getSchedule(): \Symfony\Component\Scheduler\Schedule
    {
        return $this->schedule;
    }
};

$messageGenerator = new \Symfony\Component\Scheduler\Generator\MessageGenerator(
    $scheduleProvider,
    'scheduler.main',
    new \Symfony\Component\Clock\NativeClock()
);

$schedulerTransport = new \Symfony\Component\Scheduler\Messenger\SchedulerTransport(
    $messageGenerator
);

// Manual infinite loop
while (true) {
    // Retrieve messages whose cron matches the current time
    $envelopes = $schedulerTransport->get();
    
    foreach ($envelopes as $envelope) {
        logger()->info('📨 Scheduled message dispatched', [
            'class' => get_class($envelope->getMessage()),
        ]);

        try {
            // Remove ReceivedStamp if it exists so that SendMessageMiddleware
            // agrees to route the message to ElegantTransport
            $envelope = $envelope
                ->withoutAll(\Symfony\Component\Messenger\Stamp\ReceivedStamp::class)
                ->withoutAll(\Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp::class);

            $bus->dispatch($envelope);

        } catch (\Throwable $e) {
            logger()->error('❌ Scheduled dispatch error', [
                'class' => get_class($envelope->getMessage()),
                'error' => $e->getMessage(),
            ]);
        }

        $schedulerTransport->ack($envelope);
    }

    unset($envelopes, $envelope);
    gc_collect_cycles();

    // Check for the restart signal
    if (file_exists(root_path('temp/restart_scheduler.txt'))) {
        unlink(root_path('temp/restart_scheduler.txt'));
        logger()->info("Restart signal received — stopping the scheduler.");
        break;
    }

    // Wait before the next tick — 1 second is enough for 1-minute precision
    sleep(1);
}

flock($fp, LOCK_UN);
fclose($fp);