<?php
namespace Clicalmani\Flesco\Console\Commands\Local;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dev',
    description: 'Start web server',
    hidden: false
)]
class MigrateCommand extends Command
{
    protected static $defaultDescription = 'Start database migration';

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
