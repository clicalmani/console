<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * Create a new middleware service
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:middleware',
    description: 'Create a new middleware service.',
    hidden: false
)]
class MakeMiddlewareCommand extends Command
{
    private $middlewares_path;

    public function __construct(protected $root_path)
    {
        $this->middlewares_path = $this->root_path . '/app/Http/Middlewares';
        $this->mkdir($this->middlewares_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $input->getArgument('name');

        $filename = $this->middlewares_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Middleware.sample"), [
                    'middleware' => $name
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
        $this->setHelp('Create new middle');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Middleware name')
        ]);
    }
}
