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
 * EventListener Command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:message-handler',
    description: 'Make a messenger message handler.',
    hidden: false
)]
class MakeHandlerCommand extends Command
{
    private $handlersPath;

    public function __construct(protected $rootPath)
    {
        $this->handlersPath = $this->rootPath . '/app/Handlers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->handlersPath);
        
        $handler = $input->getArgument('handler');
        $message = $input->getArgument('message');

        $filename = $this->handlersPath . '/' . $handler . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/MessageHandler.sample"), [
                    'handlerClass' => basename($handler),
                    'messageClass' => basename($message)
                ])
            )
        );

        if ($success) {
            $output->writeln('<info>Handler created successfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('handler', InputArgument::REQUIRED, 'Handler name'),
            new InputArgument('message', InputArgument::REQUIRED, 'Message name'),
        ]);
    }
}
