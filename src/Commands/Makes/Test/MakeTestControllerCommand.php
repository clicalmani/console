<?php
namespace Clicalmani\Console\Commands\Makes\Test;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Fundation\Sandbox\Sandbox;

/**
 * Create a new test controller
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:test',
    description: 'Create a new test controller in the controllers directory.',
    hidden: false
)]
class MakeTestControllerCommand extends Command
{
    private $controller_path;

    public function __construct(protected $root_path)
    {
        parent::__construct($root_path);
        
        $this->controller_path = $this->root_path . '/test/Controllers';
        $this->mkdir($this->root_path . '/test');
        $this->mkdir($this->root_path . '/test/Controllers');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $controller   = $input->getArgument('controller');
        $test_controller = $this->getClass($controller) . 'Test';

        $reflection = new \ReflectionClass(\Clicalmani\Fundation\Http\Requests\RequestController::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $inherited = ['test'];

        foreach ($methods as $method) {
            $inherited[] = $method->name;
        }

        $reflection = new \ReflectionClass("App\\Http\\Controllers\\$controller");
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $args = [];

        foreach ($methods as $method) {
            if ( !in_array($method->name, $inherited) ) $args[] = $method->name;
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
            $this->controller_path . "/$test_controller.php", 
            ltrim( Sandbox::eval(file_get_contents( dirname( __DIR__) . "/Samples/Test/Controller.sample"), [
                'test'       => $test_controller,
                'controller' => $this->getClass($controller),
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
        $this->addArgument('controller', InputArgument::REQUIRED, 'Controller to test');
    }
}
