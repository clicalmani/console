<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
    public function __construct(protected $root_path)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Clearing database');

        $filename = $input->getArgument('name');
        $tonka = new \Clicalmani\Foundation\Support\Facades\Tonka;
        $tonka->setOutput($output);

        try {
            $tonka->clearDB($filename);
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Clear database');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Migration file name')
        ]);
    }
}
