<?php
namespace Clicalmani\Console\Commands\Local;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:fresh',
    description: 'Database migration command',
    hidden: false
)]
class MigrateFreshCommand extends Command
{
    private $database_path;

    public function __construct(private $root_path)
    {
        global $dotenv;

        /**
         * Inject class dependencies
         */
        new \Clicalmani\Container\SPL_Loader( $this->root_path );

        /**
         * Load environment variables
         */
        $dotenv = \Dotenv\Dotenv::createImmutable( $this->root_path );
        $dotenv->safeLoad();

        /**
         * Include helpers
         */
        \Clicalmani\Flesco\Support\Helper::include();

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

        try {
            $output->writeln('Migrating the database ...');

            foreach (new \RecursiveIteratorIterator($migrations_dir) as $file) { 
                $pathname = $file->getPathname();

                if($file->isFile()) {
                    $filename = $file->getFileName(); 
                    
                    if(is_readable($pathname)) {
                        $migration = require $pathname;

                        if ( method_exists($migration, 'in') ) {
                            $output->writeln('Migrating ' . $filename);
                            $migration->in();
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

        if ($input->hasOption('seed')) {
            $db_seed = new ArrayInput([
                'command' => 'db:seed'
            ]);

            return $this->getApplication()->doRun($db_seed, $output);
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Run a fresh database migration');
        $this->setDefinition([
            new InputOption('seed', null, InputOption::VALUE_OPTIONAL, 'Run seeds after migration')
        ]);
    }
}
