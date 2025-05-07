<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:fresh',
    description: 'Migrate a fresh database by dropping all table and creating new ones out of the box.',
    hidden: false
)]
class MigrateFreshCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        $this->mkdir($this->rootPath . '/database/migrations');
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $filename = $input->getArgument('name');
        $tonka = new \Clicalmani\Foundation\Maker\Logic\Tonka;
        $tonka->setOutput($output);
        $tonka->setDumpFile($input->getOption('output'));

        $db_seed = new ArrayInput([
            'command' => 'db:clear',
            'name' => $filename
        ]);

        if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
            return Command::FAILURE;
        }
        
        try {
            $tonka->migrate($filename);
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        if (false !== $input->getOption('seed')) {
            $db_seed = new ArrayInput([
                'command' => 'db:seed',
                '--file' => $filename
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }
        }

        if (false !== $input->getOption('create-routines')) {
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
            new InputArgument('name', InputArgument::REQUIRED, 'Migration file name'),
            new InputOption('seed', null, InputOption::VALUE_NONE, 'Run seeds after migration'),
            new InputOption('create-routines', null, InputOption::VALUE_NONE, 'Migrate routines'),
            new InputOption('output', null, InputOption::VALUE_REQUIRED, 'Dump the generated SQL into a file')
        ]);
    }
}
