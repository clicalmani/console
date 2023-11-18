<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Sandbox\Sandbox;

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

    public function __construct(protected $root_path)
    {
        parent::__construct($root_path);
        $this->controllers_path = $this->root_path . '/app/http/controllers';
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name        = $input->getArgument('name');
        $is_api      = $input->getOption('api');
        $resource    = $input->getOption('resource');
        $namespace   = "App\\Http\\Controllers" . ($this->getPath($name) ? "\\" . $this->getPath($name): '');
        $model_class = "App\\Models\\$resource";
        $parameter   = null;
        $file_path   = $this->controllers_path . ($this->getPath($name) ? '/' . $this->getPath($name): '');

        if ( ! file_exists($file_path) ) {
            mkdir($file_path);
        }

        $filename = $file_path . '/' . $this->getClass($name) . '.php';

        if ( $is_api ) $sample = 'ControllerApi.sample';

        if ( $resource ) {
            $sample = 'ControllerResource.sample';
            $parameter = strtolower($resource);
        }

        if ( !$is_api && !$resource ) $sample = 'Controller.sample';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/$sample"), [
                'controller'  => $this->getClass($name),
                'resource'    => $this->getClass((string) $resource),
                'namespace'   => $namespace,
                'model_class' => $model_class,
                'parameter'   => $parameter
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
