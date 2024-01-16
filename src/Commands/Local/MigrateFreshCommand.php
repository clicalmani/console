<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Flesco\Misc\RecursiveFilter;
use Symfony\Component\Console\Attribute\AsCommand;
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

    public function __construct(protected $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $db_seed = new ArrayInput([
            'command' => 'db:clear'
        ]);

        if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
            return Command::FAILURE;
        }

        try {
            $migrations_dir = new \RecursiveDirectoryIterator($this->database_path . '/migrations');
            $filter = new RecursiveFilter($migrations_dir);
            $output->writeln('Migrating the database ...');

            foreach ($filter->getFiles() as $filename => $pathname) {
                $migration = require $pathname;
                if ( method_exists($migration, 'in') ) {
                    $output->writeln('Migrating ' . $filename);
                    $migration->in();
                    $output->writeln('success');
                }
            }
        } catch (\PDOException $e) {
            $output->writeln('Failure');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        if (false !== $input->getOption('seed')) {
            $db_seed = new ArrayInput([
                'command' => 'db:seed'
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }
        }

        if (false !== $input->getOption('routines')) {
            $db_seed = new ArrayInput([
                'command' => 'migrate:functions'
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }

            $db_seed = new ArrayInput([
                'command' => 'migrate:procedures'
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }

            $db_seed = new ArrayInput([
                'command' => 'migrate:views'
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Run a fresh database migration');
        $this->setDefinition([
            new InputOption('seed', null, InputOption::VALUE_NONE, 'Run seeds after migration'),
            new InputOption('routines', null, InputOption::VALUE_NONE, 'Migrate routines')
        ]);
    }
}
