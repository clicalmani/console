<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Fundation\Sandbox\Sandbox;

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
    
    public function __construct(protected $root_path)
    {
        parent::__construct($root_path);
        $this->requests_path = $this->root_path . '/app/Http/Requests';
        $this->mkdir($this->requests_path);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');
        $path = $this->requests_path . ($this->getPath($name) ? '/' . $this->getPath($name): '');
        
        if ( $path && !file_exists($path) ) {
            mkdir($path);
        }

        $filename = $path . '/' . $this->getClass($name) . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Request.sample"), [
                'request' => $this->getClass($name),
                'namespace' => 'App\\Http\Requests' . ($this->getPath($name) ? "\\" . $this->getPath($name): '')
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
