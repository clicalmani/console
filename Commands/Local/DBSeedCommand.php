<?php
namespace Clicalmani\Console\Commands\Local;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run a database seed
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'db:seed',
    description: 'Start database seeding',
    hidden: false
)]
class DBSeedCommand extends Command
{
    private $database_path;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ($class = $input->getOption('class')) {
            require_once $this->database_path . "/seeders/$class.php";

            $seeder = new $class;

            $output->writeln('Running ' . $class);

            if ( $this->runSeed($seeder, $output) ) {
                $output->writeln('success');

                return Command::SUCCESS;
            }

            $output->writeln('failure');

            return Command::FAILURE;
        }

        try {
            $seeders_dir = new \RecursiveDirectoryIterator($this->database_path . '/seeders');

            $output->writeln('Seeding the database');

            foreach (new \RecursiveIteratorIterator($seeders_dir) as $file) { 
                $pathname = $file->getPathname();

                if($file->isFile()) {
                    $filename = $file->getFileName();
                    $class = substr($filename, 0, strlen($filename) - 4); 
                    
                    if(is_readable($pathname)) {
                        require $this->root_path . "/database/seeders/$class.php";

                        $classNs = "\Database\Seeders\\$class";
                        $seeder = new $classNs;

                        $output->writeln('Seeding ' . $class);

                        if ( $this->runSeed($seeder, $output) ) {
                            $output->writeln('success');
                        } else {
                            $output->writeln('failure');
                        }
                    }
                }
            }

            return Command::SUCCESS;

        } catch(\PDOException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function configure() : void
    {
        $this->setHelp('Run a database seed.');
        $this->setDefinition([
            new InputOption('class', null, InputOption::VALUE_REQUIRED, 'Seeder class')
        ]);
    }

    public function runSeed(mixed $seeder, OutputInterface $output) : bool
    {
        try {
            $seeder->run();
            return true;
        } catch(\PDOException $e) {
            $output->writeln($e->getMessage());
            return false;
        }
    }
}
