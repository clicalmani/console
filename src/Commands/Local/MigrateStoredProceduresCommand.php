<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Support\Facades\Tonka;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Start routine functions migration
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'migrate:procedures',
    description: 'Migrate routine procedures',
    hidden: false
)]
class MigrateStoredProceduresCommand extends Command
{
    public function __construct(protected $rootPath) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        Tonka::setOutput($output);
        return Tonka::routineProcs() ? Command::SUCCESS: Command::FAILURE;
    }
}
