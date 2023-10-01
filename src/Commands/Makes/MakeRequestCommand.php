<?php
namespace Clicalmani\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Sandbox\Sandbox;

/**
 * Create a new request class.
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:request',
    description: 'Create a new request class.',
    hidden: false
)]
class MakeRequestCommand extends Command
{
    private $requests_path;
    
    public function __construct(private $root_path)
    {
        $this->requests_path = $this->root_path . '/app/http/requests';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $request  = $input->getArgument('name');
        $filename = $this->requests_path . '/' . $request . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Request.sample"), [
                'request' => $request
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
        $this->setHelp('Create new request');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Request name')
        ]);
    }
}
