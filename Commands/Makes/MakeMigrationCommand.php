<?php
namespace Clicalmani\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make migration command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:migration',
    description: 'Create a database migration.',
    hidden: false
)]
class MakeMigrationCommand extends Command
{
    private $database_path, $filename;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->filename = $this->database_path . '/migrations/' . date('Y_m_d') . '_' . time() . '_' . $input->getArgument('filename') . '.php';

        $success = file_put_contents($this->filename, file_get_contents( __DIR__ . '/Samples/Migration.sample'));

        if ($success) {

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('A file will be created in database migrations path with the given name.');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The file name for migration');
    }
}
