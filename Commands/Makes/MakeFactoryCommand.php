<?php
namespace Clicalmani\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Misc\Tools;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create new database seeder factory
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:factory',
    description: 'Create a new database seeder factory.',
    hidden: false
)]
class MakeFactoryCommand extends Command
{
    private $database_path;
    
    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $factory  = $input->getArgument('name');
        
        # Create factories directory if not exists.
        if ( !file_exists( $this->database_path . '/factories' ) ) {
            mkdir( $this->database_path . '/factories' );
        }

        $filename = $this->database_path . '/factories/' . $factory . '.php';
        $sample   = 'Factory.sample';
        $model    = null;

        if ( $model = $input->getOption('model') ) {
            $sample = 'FactoryModel.sample';
        }

        $success = file_put_contents(
            $filename, 
            ltrim( Tools::eval(file_get_contents( __DIR__ . "/Samples/$sample"), [
                'factory' => $factory,
                'model'   => $model
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
        $this->setHelp('Create new database factory');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Factory name'),
            new InputOption('model', null, InputOption::VALUE_REQUIRED, 'Factory model')
        ]);
    }
}
