<?php
namespace Clicalmani\Console\Commands\Makes\Test;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Misc\Tools;

/**
 * Create a new test controller
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:test_controller',
    description: 'Create a new test controller in the controllers directory.',
    hidden: false
)]
class MakeTestControllerCommand extends Command
{
    private $controllers_path;

    public function __construct(private $root_path)
    {
        $this->controllers_path = $this->root_path . '/app/test/controllers';

        if ( ! file_exists($this->controllers_path) ) {
            $test_path = $this->root_path . '/app/test';

            if ( ! file_exists($test_path) ) {
                mkdir($test_path);
            }

            mkdir($this->controllers_path);
        }

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $controller   = $input->getArgument('name');
        $test_controller = substr($controller, strripos($controller, '\\')) . 'Test';

        $reflection = new \ReflectionClass(\Clicalmani\Flesco\Http\Controllers\RequestController::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $inherited = [];

        foreach ($methods as $method) {
            $inherited[] = $method->name;
        }

        $reflection = new \ReflectionClass("App\\Http\\Controllers\\$controller");
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $args = [];

        foreach ($methods as $method) {
            if ( !in_array($method->name, $inherited) ) $args[] = $method->name;
        }
        
        $sample = 'Controller.sample';

        if ($auth = $input->getOption('auth')) {
            $sample = 'ControllerAuth.sample';
        }

        $methods = function($args) {
            $ret = '';
            foreach ($args as $index => $method) {
                if ($index == 0) $ret .= "\t\r";
                $ret .= <<<METHOD
                    /**
                     * Seed $method method
                     * 
                     * @return array 
                     */
                    public function $method() : array
                    {
                        return [
                            // Action parameters
                        ];
                    }
                METHOD;

                if ($index < count($args) - 1) $ret .= "\n\n";
            }

            $ret .= "\n\n";

            $ret .= <<<METHOD
                /**
                 * Test method
                 * 
                 * @return void 
                 */
                public static function test() : void
                {
                    // Test code
                }
            METHOD;
            
            return $ret;
        };

        $methods = $methods($args);

        $success = file_put_contents(
            $this->controllers_path . "/$test_controller.php", 
            ltrim( Tools::eval(file_get_contents( dirname( __DIR__) . "/Samples/Test/$sample"), [
                'test'       => $test_controller,
                'controller' => substr($controller, strripos($controller, '\\')),
                'class'      => $controller,
                'methods'    => $methods
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
        $this->setHelp('Create new test controller');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Controller to test'),
            new InputOption('auth', null, InputOption::VALUE_NONE, 'Enable user authentication')
        ]);
    }
}
