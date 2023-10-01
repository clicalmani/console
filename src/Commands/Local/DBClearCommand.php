<?php
namespace Clicalmani\Console\Commands\Local;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all database tables
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'db:clear',
    description: 'Clear database',
    hidden: false
)]
class DBClearCommand extends Command
{
    private $database_path;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $migrations_dir = new \RecursiveDirectoryIterator($this->database_path . '/migrations');

        try {
            $output->writeln('Cleaning the database ...');

            foreach (new \RecursiveIteratorIterator($migrations_dir) as $file) { 
                $pathname = $file->getPathname();

                if($file->isFile()) {
                    $filename = $file->getFileName(); 
                    
                    if(is_readable($pathname)) {
                        $migration = require $pathname;

                        if ( method_exists($migration, 'out') ) {
                            $output->writeln('Droping ' . $filename);
                            $migration->out();
                            $output->writeln('success');
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            $output->writeln('Failure');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
