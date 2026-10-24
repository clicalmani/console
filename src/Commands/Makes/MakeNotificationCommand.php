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
    name: 'make:notification',
    description: 'Create a notification class',
    hidden: false
)]
class MakeNotificationCommand extends Command
{
    private $notificationsPath;

    public function __construct(protected $rootPath)
    {
        $this->notificationsPath = $this->rootPath . '/app/Notifications';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->notificationsPath);
        
        $filename = $this->notificationsPath . '/' . $input->getArgument('name') . '.php';

        $sample = $input->getOption('broadcast') ? "/Samples/NotificationWithBroadcasting.sample": "/Samples/Notification.sample";

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . $sample), [
                    'className' => basename($input->getArgument('name'))
                ])
            )
        );

        if ($success) {
            $output->writeln('<info>Notification created successfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create a custom event');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Notification name.'),
            new InputOption('broadcast', 'b', InputOption::VALUE_NONE, 'Broadcasting notification'),
        ]);
    }
}
