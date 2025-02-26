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
    name: 'task:list',
    description: 'List the different task in a tabular format.',
    hidden: false
)]
class ListCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $list_command = new ArrayInput([
            'command' => 'schedule:list',
            '--format' => $input->getOption('format') ?? 'text'
        ]);

        return $this->getCrunzApplication()->doRun($list_command, $output);
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputOption('format', null, InputOption::VALUE_REQUIRED, 'Change list format', 'text')
        ]);
    }
}
