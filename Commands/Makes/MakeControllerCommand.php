<?php
namespace Clicalmani\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Misc\Tools;

/**
 * Create a new controller
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:controller',
    description: 'Create a new controller in the controllers directory.',
    hidden: false
)]
class MakeControllerCommand extends Command
{
    private $controllers_path;

    public function __construct(private $root_path)
    {
        $this->controllers_path = $this->root_path . '/app/http/controllers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $controller   = $input->getArgument('name');
        $is_api       = $input->getOption('api');
        $resource     = $input->getOption('resource');
        $class        = "\\App\\Http\\Controllers\\$resource";
        $model_class  = "App\\Models\\$resource";
        $parameter    = null;

        $filename = $this->controllers_path . '/' . $controller . '.php';

        if ( $is_api ) $sample = 'ControllerApi.sample';

        if ( $resource ) {
            $sample = 'ControllerResource.sample';
            $parameter = strtolower($resource);
        }

        if ( !$is_api && !$resource ) $sample = 'Controller.sample';

        $success = file_put_contents(
            $filename, 
            ltrim( Tools::eval(file_get_contents( __DIR__ . "/Samples/$sample"), [
                'controller'   => $controller,
                'resource'     => $resource,
                'class'        => $class,
                'model_class'  => $model_class,
                'parameter'    => $parameter
            ]) )
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
        $this->setHelp('Create new controller');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Controller name'),
            new InputOption('api', null, InputOption::VALUE_NONE, 'Api Controller'),
            new InputOption('resource', null, InputOption::VALUE_REQUIRED, 'Resource Controller')
        ]);
    }
}
