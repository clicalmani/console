<?php
namespace Clicalmani\Console\Commands\Routines;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Sandbox\Sandbox;

/**
 * Create a database routine
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'db:function',
    description: 'Create database routine',
    hidden: false
)]
class RoutineFunctionCommand extends Command
{
    private $database_path, $functions_path;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';

        if ( !file_exists($this->database_path . '/routines') ) {
            mkdir($this->database_path . '/routines');
        }

        if ( !file_exists($this->database_path . '/routines/functions') ) {
            mkdir($this->database_path . '/routines/functions');
        }

        $this->functions_path = $this->database_path . '/routines/functions';

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $function   = $input->getArgument('name');
        $filename = $this->functions_path . "/$function";
        
        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Function.sample"), [
                'name'   => $function
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
        $this->setHelp('Create database routine');
        $this->addArgument('name', InputArgument::REQUIRED, 'Function name');
    }
}
