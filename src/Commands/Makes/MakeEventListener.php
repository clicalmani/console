<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;
use Symfony\Component\Console\Input\InputOption;

/**
 * EventListener Command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'event:listener',
    description: 'Create an event listener class.',
    hidden: false
)]
class MakeEventListener extends Command
{
    private $listenersPath;

    public function __construct(protected $rootPath)
    {
        $this->listenersPath = $this->rootPath . '/app/Listeners';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->listenersPath);
        
        $name = $input->getArgument('name');
        $event = $input->getArgument('event');

        $filename = $this->listenersPath . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/EventListener.sample"), [
                    'class'      => $name,
                    'eventClass' => $event
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
        $this->setHelp('Create a custom command');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Listener class name'),
            new InputArgument('event', InputArgument::REQUIRED, 'Event class')
        ]);
    }
}
