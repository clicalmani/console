<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HelpCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'task:run',
    description: 'Run one or more tasks',
    hidden: false
)]
class RunCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $command = new ArrayInput([
            'command' => 'schedule:run',
            '--task' => $input->getOption('task'),
            '--force' => $input->getOption('force')
        ]);

        return $this->getCrunzApplication()->doRun($command, $output);
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputOption('task', null, InputOption::VALUE_REQUIRED, 'Task index', 1),
            new InputOption('force', null, InputOption::VALUE_NONE, 'Force run a single task, use the task:list command to determine the Task number and run as follows')
        ]);
    }
}
