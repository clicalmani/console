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
    name: 'make:message',
    description: 'Make a messenger message.',
    hidden: false
)]
class MakeMessageCommand extends Command
{
    private $messagePath;
    private $handlersPath;

    public function __construct(protected $rootPath)
    {
        $this->messagePath = $this->rootPath . '/app/Messages';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->messagePath);
        
        $message = $input->getArgument('message');
        $handler = $input->getOption('handler');

        $filename = $this->messagePath . '/' . $message . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Message.sample"), [
                    'message' => basename($message)
                ])
            )
        );

        if ($success) {
            $output->writeln('<info>Message created successfully</info>');

            if ($handler) {
                $handlerMaker = new ArrayInput([
                    'command' => 'make:message-handler',
                    'messageClass' => basename($message),
                    'handlerClass' => basename($handler)
                ]);

                if (0 === $this->getApplication()->doRun($handlerMaker, $output)) {
                    $output->writeln('<info>Handler created successfully</info>');
                }
            }

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('message', InputArgument::REQUIRED, 'Message name'),
            new InputOption('handler', 'h', InputOption::VALUE_REQUIRED, 'Handler name')
        ]);
    }
}
