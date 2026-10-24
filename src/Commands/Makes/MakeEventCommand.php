<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Core\Sandbox\Sandbox;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a custom event command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:event',
    description: 'Create a custom event',
    hidden: false
)]
class MakeEventCommand extends Command
{
    private $eventsPath;
    private $listenersPath;

    public function __construct(protected $rootPath)
    {
        $this->eventsPath = $this->rootPath . '/app/Events';
        $this->listenersPath = $this->rootPath . '/app/Listeners';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->eventsPath);
        $this->mkdir($this->listenersPath);
        
        $event = $this->eventsPath . '/' . $input->getArgument('event') . '.php';
        $listener = null;

        if ($option = $input->getOption('listener')) {
            $listener = $this->listenersPath . '/' . $option . '.php';
        }

        $sample = $input->getOption('broadcasting') ? "/Samples/BroadcastingEvent.sample": "/Samples/Event.sample";

        $success = file_put_contents(
            $event, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . $sample), [
                    'event' => basename($input->getArgument('event'))
                ])
            )
        );

        if ($success) {
            $output->writeln('<info>Event created successfully</info>');

            if ($listener) {

                $listenerMaker = new ArrayInput([
                    'command' => 'make:event-listener',
                    'name' => basename($input->getOption('listener')),
                    'event' => basename($input->getArgument('event'))
                ]);

                if (0 === $this->getApplication()->doRun($listenerMaker, $output)) {
                    $output->writeln('<info>Listener created successfully</info>');
                }
            }

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create a custom event');
        $this->setDefinition([
            new InputArgument('event', InputArgument::REQUIRED, 'Custom event.'),
            new InputOption('listener', 'l', InputOption::VALUE_REQUIRED, 'Event Listener.'),
            new InputOption('broadcast', 'b', InputOption::VALUE_NONE, 'Broadcasting event'),
        ]);
    }
}
