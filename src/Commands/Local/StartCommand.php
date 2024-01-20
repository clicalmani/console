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

    public function __construct(protected $root_path)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $port = $input->getOption('port');
        $success = shell_exec("php -S localhost:$port server.php");

        if ($success) return Command::SUCCESS;

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('This command start the web server');
        $this->setDefinition([
            new InputOption('port', 'p', InputOption::VALUE_REQUIRED, 'Host port', 8000)
        ]);
    }
}
