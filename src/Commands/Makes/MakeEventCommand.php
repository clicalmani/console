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
    name: 'make:model-event-listener',
    description: 'Create a custom model event listener',
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
        $listener = $input->getArgument('listener');

        $filename = $this->events_path . '/' . $listener . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/ModelEvent.sample"), [
                    'listener' => $listener,
                    'event' => $input->getArgument('event')
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
            new InputArgument('listener', InputArgument::REQUIRED, 'Listener class name.'),
            new InputArgument('event', InputArgument::REQUIRED, 'Custom event.'),
        ]);
    }
}
