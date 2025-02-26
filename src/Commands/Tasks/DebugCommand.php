<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DebugCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'task:debug',
    description: 'Show basic information about task.',
    hidden: false
)]
class DebugCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $list_command = new ArrayInput([
            'command' => 'task:debug',
            'index' => $input->getArgument('index')
        ]);

        return $this->getCrunzApplication()->doRun($list_command, $output);
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('index', InputArgument::REQUIRED, 'The index of the task to debug')
        ]);
    }
}
