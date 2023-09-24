<?php
namespace Clicalmani\Flesco\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Misc\Tools;

#[AsCommand(
    name: 'make:middleware',
    description: 'The make command is the main command for launching verious tasks, such as creation of models, controllers, servicies, events and migration.',
    hidden: false
)]
class MakeMiddlewareCommand extends Command
{
    public function __construct(private $root_path)
    {
        $this->middlewares_path = $this->root_path . '/app/http/middlewares';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $middleware = $input->getArgument('name');
        $handler    = $input->getArgument('handler');

        $filename = $this->middlewares_path . '/' . $middleware . '.php';

        $success = file_put_contents(
            $filename, 
            Tools::eval(file_get_contents( __DIR__ . "/Samples/Middleware.sample"), [
                'middleware' => $middleware,
                'handler'    => $handler
            ])
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
            new InputArgument('name', InputArgument::REQUIRED, 'Middleware name'),
            new InputArgument('handler', InputArgument::REQUIRED, 'Handler file name')
        ]);
    }
}
