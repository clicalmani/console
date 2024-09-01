<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * Create a new helper service
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:helper',
    description: 'Create a new helper service.',
    hidden: false
)]
class MakeHelperCommand extends Command
{
    private $providers_path;

    public function __construct(protected $root_path)
    {
        $this->providers_path = $this->root_path . '/app/Providers';
        $this->mkdir($this->providers_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');

        $filename = $this->providers_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Helper.sample"), [
                    'class' => $name
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
        $this->setHelp('Create a new helper service');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Helper name')
        ]);
    }
}
