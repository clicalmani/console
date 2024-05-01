<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Flesco\Misc\RecursiveFilter;
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
    private $database_path;

    public function __construct(protected $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ($seeder = $input->getOption('seeder')) {
            require_once $this->database_path . "/seeders/$seeder.php";

            $seederNs = "\Database\Seeders\\$seeder";
            $seeder = new $seederNs;

            $output->writeln($this->formatOutput('Running ' . $seeder));

            if ( $this->runSeed($seeder, $output) ) {
                $output->writeln($this->formatOutput('Success'));

                return Command::SUCCESS;
            }

            $output->writeln($this->formatOutput('Failed'));

            return Command::FAILURE;
        }

        try {
            $seeders_dir = new \RecursiveDirectoryIterator($this->database_path . '/seeders');
            $filter = new RecursiveFilter($seeders_dir);
            $filter->setPattern("\\.php$");

            $output->writeln($this->formatOutput('Seeding the database'));

            foreach (new \RecursiveIteratorIterator($filter) as $file) {

                $pathname = $file->getPathname();
                $filename = $file->getFilename();
                
                if(is_readable($pathname)) {
                    $class = substr($filename, 0, strlen($filename) - 4);
                    $classNs = "\Database\Seeders\\$class";
                    $seeder = new $classNs;

                    $output->writeln($this->formatOutput('Running ' . $class));

                    if ( $this->runSeed($seeder, $output) ) {
                        $output->writeln($this->formatOutput('Success'));
                    } else {
                        $output->writeln($this->formatOutput('Failure'));
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
            new InputOption('seeder', null, InputOption::VALUE_REQUIRED, 'Seeder class')
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
