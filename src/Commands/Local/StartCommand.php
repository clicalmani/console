<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Start dev server console command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'dev',
    description: 'Start web server',
    hidden: false
)]
class StartCommand extends Command
{
    protected static $defaultDescription = 'Start the server';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ($input->getOption('worker')) {
            if (NULL !== $worker = $input->getOption('file')) {
                //
            } else {
                $output->writeln('A worker file must be specified');
                return Command::FAILURE;
            }
        }

        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $success = `php -S {$host}:{$port} server.php`;
        
        if ($success) return Command::SUCCESS;

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('This command start the web server');
            $this->setDefinition([
            new InputOption('host', null, InputOption::VALUE_REQUIRED, 'Host address', 'localhost'),
            new InputOption('port', 'p', InputOption::VALUE_REQUIRED, 'Host port', 8000),
            new InputOption('worker', 'w', InputOption::VALUE_NONE, 'Start a worker command'),
            new InputOption('file', 'f', InputOption::VALUE_REQUIRED, 'Worker file')
        ]);
    }
}
