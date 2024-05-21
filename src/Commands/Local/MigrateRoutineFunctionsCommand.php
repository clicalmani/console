<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Start routine functions migration
 * 
 * @package Clcialmani\Console
 * @author @clicalmani
 */
#[AsCommand(
    name: 'migrate:functions',
    description: 'Migrate routine functions',
    hidden: false
)]
class MigrateRoutineFunctionsCommand extends Command
{
    public function __construct(protected $root_path) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $tonka = new \Clicalmani\Fundation\Logic\Internal\Tonka;
        $tonka->setOutput($output);
        return $tonka->routineFunctions() ? Command::SUCCESS: Command::FAILURE;
    }
}
