<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * Create a custom event command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:event',
    description: 'Create a custom event.',
    hidden: false
)]
class MakeEventCommand extends Command
{
    private $events_path;

    public function __construct(protected $rootPath)
    {
        $this->events_path = $this->rootPath . '/app/Events';
        $this->mkdir($this->events_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');

        $filename = $this->events_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Event.sample"), [
                    'custom_event' => $name
                ])
            )
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
        $this->setHelp('Create a custom event');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Custom event name')
        ]);
    }
}
