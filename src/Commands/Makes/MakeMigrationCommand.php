<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Sandbox\Sandbox;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
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
    private $migrations_path;

    public function __construct(protected $root_path)
    {
        $this->migrations_path = $this->root_path . '/database/migrations';
        $this->mkdir($this->migrations_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $table    = $input->getArgument('table');
        $filename = $this->migrations_path . '/' . date('Y_m_d') . '_' . time() . '_' . strtolower($table) . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . '/Samples/Migration.sample'), [
                'table'   => $table
            ]) )
        );

        if ($success) {

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create a new migration for a database table.');
        $this->addArgument('table', InputArgument::REQUIRED, 'Database table name');
    }
}
