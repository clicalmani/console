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
 * Create a new middleware service
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:command',
    description: 'Create a custom command.',
    hidden: false
)]
class MakeCommandCommand extends Command
{
    private $commands_path;

    public function __construct(protected $rootPath)
    {
        $this->commands_path = $this->rootPath . '/app/Commands';
        $this->mkdir($this->commands_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');
        $id   = $input->getArgument('id');
        $desc = $input->getOption('description') ?? '';
        $hidden = $input->getOption('hidden') ?? false;

        $filename = $this->commands_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Command.sample"), [
                    'command' => $name,
                    'name'    => $id,
                    'desc'    => $desc,
                    'hidden'  => $hidden
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
            new InputArgument('name', InputArgument::REQUIRED, 'Command name'),
            new InputArgument('id', InputArgument::REQUIRED, 'Event ID'),
            new InputOption('description', null, InputOption::VALUE_OPTIONAL, 'Event description'),
            new InputOption('hidden', null, InputOption::VALUE_NONE, 'Create a hidden event')
        ]);
    }
}
