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
 * Mail Command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:mail',
    description: 'Create a mailable class.',
    hidden: false
)]
class MakeMailCommand extends Command
{
    private $mailPath;

    public function __construct(protected $rootPath)
    {
        $this->mailPath = $this->rootPath . '/app/Mail';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->mailPath);
        
        $name = $input->getArgument('name');
        $filename = $this->mailPath . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Mail.sample"), [
                    'className'      => basename($name)
                ])
            )
        );

        if ($success) {
            $output->writeln('<info>Mail created successfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Mail class name')
        ]);
    }
}
