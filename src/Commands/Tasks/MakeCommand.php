<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MakeCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'task:make',
    description: 'Generate a task file skeleton',
    hidden: false
)]
class MakeCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $command = new ArrayInput([
            'command' => 'make:task',
            'taskfile' => $input->getArgument('name'),
            '--run' => $input->getOption('run'),
            '--in' => $input->getOption('in'),
            '--frequency' => $input->getOption('frequency'),
            '--constraint' => $input->getOption('constraint'),
            '--type' => $input->getOption('type') ?? 'basic',
            '--description' => $input->getOption('description') ?? 'Task description'
        ]);

        return $this->getCrunzApplication()->doRun($command, $output);
        $cwd = $this->rootPath . '/vendor/bin';
        chdir($cwd);

        $command = 'crunz make:task ' . $input->getArgument('name');

        if ($run = $input->getOption('run')) {
            $command .= ' --run ' . $run;
        }

        if ($in = $input->getOption('in')) {
            $command .= " --in $in";
        }

        if ($frequency = $input->getOption('frequency')) {
            $command .= ' --frequency ' . $frequency;
        }

        if ($constraint = $input->getOption('constraint')) {
            $command .= ' --constraint ' . $constraint;
        }
        
        shell_exec($command);

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Task file name without the Task prefix'),
            new InputOption('run', 'r', InputOption::VALUE_REQUIRED, 'The script to run'),
            new InputOption('in', 'i', InputOption::VALUE_REQUIRED, 'Script directory'),
            new InputOption('frequency', 'f', InputOption::VALUE_REQUIRED, 'Command frequency'),
            new InputOption('constraint', 'c', InputOption::VALUE_REQUIRED, 'Command constraint'),
            new InputOption('description', 'd', InputOption::VALUE_REQUIRED, 'The task description, default Task description'),
            new InputOption('type', 't', InputOption::VALUE_REQUIRED, 'The task type, default basic'),
        ]);
    }
}
