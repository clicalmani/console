<?php
namespace Clicalmani\Console\Commands\Tasks\bin;

$root = dirname(__DIR__, 7);
include_once $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->database = require_once config_path('/database.php');
$app->boot();

use Clicalmani\Foundation\Filesystem\RecursiveFilter;
use Clicalmani\Task\Builder\TaskBuilderFactory;
use Clicalmani\Task\Executor\InsideProcessExecutor;
use Clicalmani\Task\Handler\TaskHandlerFactory;
use Clicalmani\Task\Lock\Lock;
use Clicalmani\Task\Lock\Storage\FileLockStorage;
use Clicalmani\Task\Runner\PendingExecutionFinder;
use Clicalmani\Task\Scheduler\TaskScheduler;
use Clicalmani\Task\Storage\ArrayStorage\ArrayTaskExecutionRepository;
use Clicalmani\Task\Storage\ArrayStorage\ArrayTaskRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Utility
$schedulesPath = $root . '/app/Scheduler';
$eventDispatcher;
$taskHandlerFactory;
$executor;
$factory;

// Locking
$storage;
$lock;

// Storage
$taskRepository;
$taskExecutionRepository;
$taskExecutionFinder;

$scheduler;

$eventDispatcher = new EventDispatcher;
$taskHandlerFactory = new TaskHandlerFactory;
$executor = new InsideProcessExecutor($taskHandlerFactory);
$factory = new TaskBuilderFactory;

$storage = new FileLockStorage(__DIR__ . '/lock');
$lock = new Lock($storage);

$taskRepository = new ArrayTaskRepository;
$taskExecutionRepository = new ArrayTaskExecutionRepository;
$taskExecutionFinder = new PendingExecutionFinder($taskExecutionRepository, $taskHandlerFactory, $lock);

$scheduler = new TaskScheduler(
    $factory,
    $taskRepository,
    $taskExecutionRepository,
    $eventDispatcher
);

$filter = new RecursiveFilter(
    new \RecursiveDirectoryIterator($schedulesPath, \RecursiveDirectoryIterator::SKIP_DOTS),
);
$filter->setPattern("\\.php$");
$filter->recurseSubFolders(false);

foreach (new \RecursiveIteratorIterator($filter) as $file) {
    $filename = $file->getFilename();
    $class = "\\App\\Scheduler\\" . substr($filename, 0, strlen($filename) - 4);
    /** @var \Clicalmani\Task\Scheduler\Schedule */
    $schedule = (new $class)->getSchedule();
    /** @var \Clicalmani\Task\Messenger\RecurringMessage */
    foreach ($schedule->getMessages() as $message) {
        $scheduler->createTask(
            $message->getHandler(),
            $message->getMessage()
        )->cron(
            $message->getCronExpression(),
            $message->getStartDate()
        )->schedule();
    }
}

\Clicalmani\Console\Commands\Tasks\Worker::run();