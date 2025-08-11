<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Task\Runner\TaskRunner;

class Worker
{
    public static function run()
    {
        global $eventDispatcher, $executor, $taskExecutionRepository, $taskExecutionFinder;

        // Initialize the task runner
        (new TaskRunner(
            $taskExecutionRepository,
            $taskExecutionFinder,
            $executor,
            $eventDispatcher
        ))->runTasks();
    }
}