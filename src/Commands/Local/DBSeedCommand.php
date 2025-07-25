<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Support\Facades\Tonka;
use Symfony\Component\Console\Attribute\AsCommand;
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
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        Tonka::setOutput($output);

        if ($file = $input->getOption('file')) 
            return Tonka::seed(null, $file) ? Command::SUCCESS: Command::FAILURE;
        return Tonka::seed($input->getOption('seeder')) ? Command::SUCCESS: Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Run a database seed.');
        $this->setDefinition([
            new InputOption('file', null, InputOption::VALUE_REQUIRED, 'Migration file name'),
            new InputOption('seeder', null, InputOption::VALUE_REQUIRED, 'Seeder class')
        ]);
    }
}
