<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Fondation\Sandbox\Sandbox;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create new database seeder
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:seeder',
    description: 'Create a new database seeder.',
    hidden: false
)]
class MakeSeederCommand extends Command
{
    private $seeders_path;
    
    public function __construct(protected $root_path)
    {
        $this->seeders_path = $this->root_path . '/database/seeders';
        $this->mkdir($this->seeders_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $seeder  = $input->getArgument('name');
        
        $filename = $this->seeders_path . '/' . $seeder . '.php';
        $sample   = 'Seeder.sample';
        $model    = null;

        if ( $factory = $input->getOption('factory') ) {
            $model = substr($factory, 0, strlen($factory) - 7);
            $sample = 'SeederModel.sample';
        }

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/$sample"), [
                'seeder' => $seeder,
                'model'  => $model
            ]) )
        );

        if ($success) {

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create new database seeder');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Seeder class name'),
            new InputOption('factory', null, InputOption::VALUE_REQUIRED, 'Seeder factory')
        ]);
    }
}
