<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Collection\Collection;
use Clicalmani\Foundation\Filesystem\RecursiveFilter;
use Clicalmani\Task\Builder\TaskBuilderFactory;
use Clicalmani\Task\Executor\InsideProcessExecutor;
use Clicalmani\Task\Handler\TaskHandlerFactory;
use Clicalmani\Task\Lock\Lock;
use Clicalmani\Task\Lock\Storage\FileLockStorage;
use Clicalmani\Task\Messenger\RecurringMessage;
use Clicalmani\Task\Runner\PendingExecutionFinder;
use Clicalmani\Task\Runner\TaskRunner;
use Clicalmani\Task\Scheduler\TaskScheduler;
use Clicalmani\Task\Storage\ArrayStorage\ArrayTaskExecutionRepository;
use Clicalmani\Task\Storage\ArrayStorage\ArrayTaskRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * DebugCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'schedule:consume',
    description: 'Consume schedules',
    hidden: false
)]
class ConsumeCommand extends Command
{
    // Utility
    private $schedulesPath;
    private $eventDispatcher;
    private $taskHandlerFactory;
    private $executor;
    private $factory;

    // Locking
    private $storage;
    private $lock;

    // Storage
    private $taskRepository;
    private $taskExecutionRepository;
    private $taskExecutionFinder;

    private $scheduler;
    private $runner;

    public function __construct(protected $rootPath)
    {
        $this->schedulesPath = $this->rootPath . '/app/Scheduler';

        $this->eventDispatcher = new EventDispatcher;
        $this->taskHandlerFactory = new TaskHandlerFactory;
        $this->executor = new InsideProcessExecutor($this->taskHandlerFactory);
        $this->factory = new TaskBuilderFactory;

        $this->storage = new FileLockStorage(__DIR__ . '/lock');
        $this->lock = new Lock($this->storage);

        $this->taskRepository = new ArrayTaskRepository;
        $this->taskExecutionRepository = new ArrayTaskExecutionRepository;
        $this->taskExecutionFinder = new PendingExecutionFinder($this->taskExecutionRepository, $this->taskHandlerFactory, $this->lock);

        $this->scheduler = new TaskScheduler(
            $this->factory,
            $this->taskRepository,
            $this->taskExecutionRepository,
            $this->eventDispatcher
        );
        $this->runner = new TaskRunner(
            $this->taskExecutionRepository,
            $this->taskExecutionFinder,
            $this->executor,
            $this->eventDispatcher
        );

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $filter = new RecursiveFilter(
            new \RecursiveDirectoryIterator($this->schedulesPath)
        );
        $filter->setPattern("\\.php$");
        $filter->recurseSubFolders(false);
        
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            $filename = $file->getFilename();
            $class = "\\App\\Scheduler\\" . substr($filename, 0, strlen($filename) - 4);
            $output->writeln('Consuming ' . $class);
            /** @var \Clicalmani\Task\Scheduler\Schedule */
            $schedule = (new $class)->getSchedule();
            $this->scheduleTasks($schedule->getMessages());
        }

        $this->runTasks();

        return Command::SUCCESS;
    }

    private function scheduleTasks(Collection $messages)
    {
        /** @var \Clicalmani\Task\Messenger\RecurringMessage */
        foreach ($messages as $message) {
            $this->scheduler->createTask(
                $message->getHandler()
            )->cron(
                $message->getCronExpression(),
                $message->getStartDate()
            )->schedule();
        }
    }

    private function runTasks()
    {
        $this->runner->runTasks();
    }
}
