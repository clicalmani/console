<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Fundation\Sandbox\Sandbox;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a new input validation service
 * 
 * @package Clicalmani\Console
 * @author @clicalmani
 */
#[AsCommand(
    name: 'make:validator',
    description: 'Create a new input validation service.',
    hidden: false
)]
class MakeValidatorCommand extends Command
{
    private $validators_path;

    public function __construct(protected $root_path)
    {
        $this->validators_path = $this->root_path . '/app/Http/Validators';
        $this->mkdir($this->validators_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');
        $argument = $input->getOption('argument');

        $filename = $this->validators_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Validator.sample"), [
                    'validator' => $name,
                    'argument' => $argument
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
        $this->setHelp('Create a new input validation service');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Validator name'),
            new InputOption('argument', 'arg', InputOption::VALUE_REQUIRED, 'Validator argument', null, ['int', 'float', 'numeric'])
        ]);
    }
}
