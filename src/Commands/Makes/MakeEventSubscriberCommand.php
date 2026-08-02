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
    name: 'event:subscriber',
    description: 'Create an event subscriber class.',
    hidden: false
)]
class MakeEventSubscriberCommand extends Command
{
    private $subscribersPath;

    public function __construct(protected $rootPath)
    {
        $this->subscribersPath = $this->rootPath . '/app/EventSubscribers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->subscribersPath);
        
        $name = $input->getArgument('name');

        $filename = $this->subscribersPath . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/EventSubscriber.sample"), [
                    'class' => $name,
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
            new InputArgument('name', InputArgument::REQUIRED, 'Subscriber class name'),
        ]);
    }
}
