<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Providers\ValidationServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;
use Clicalmani\Validation\Validator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a new input validation service
 * 
 * @package Clicalmani\Console
 * @author @clicalmani
 */
#[AsCommand(
    name: 'make:validator',
    description: 'Create a new input validation rule.',
    hidden: false
)]
class MakeValidatorCommand extends Command
{
    private $validators_path;

    public function __construct(protected $rootPath)
    {
        $this->validators_path = $this->rootPath . '/app/Http/Validators';
        $this->mkdir($this->validators_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $validator = $input->getArgument('name');
        $argument = $input->getOption('argument');
        $sample = "/Samples/Validator.sample";
        $extends = \Clicalmani\Validation\Rule::class;

        if ($input->getOption('override')) {
            $extends = ValidationServiceProvider::getValidator($argument);

            if (!$extends) {
                $output->writeln(sprintf("Could not find a rule with argument %s", $argument));
                return Command::FAILURE;
            }
        }

        $filename = $this->validators_path . '/' . $validator . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . $sample), compact('validator', 'argument', 'extends'))
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
        $this->setHelp('Create a new input validation rule');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Rule name'),
            new InputOption('argument', 'a', InputOption::VALUE_REQUIRED, 'Validator argument', null, ['int', 'float', 'numeric', 'json', 'json[]']),
            new InputOption('override', 'o', InputOption::VALUE_NONE, 'Override a builtin rule'),
        ]);
    }
}
