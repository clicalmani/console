<?php
namespace Clicalmani\Console\Commands\Local\Test;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * TestControllerCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[AsCommand(
    name: 'test:controller',
    description: 'Test a controller',
    hidden: false
)]
class TestControllerCommand extends Command
{
    private $controller_path;

    public function __construct(protected $root_path)
    {
        $this->controller_path = $this->root_path . '/test/Controllers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ($controller = $input->getOption('controller')) {
            try {
                $class = "\\Test\\Controllers\\{$controller}Test";
                $class::test();

                return Command::SUCCESS;

            } catch(\Exception $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }
        }

        try {
            $test_dir = new \RecursiveDirectoryIterator($this->controller_path);
	
            foreach (new \RecursiveIteratorIterator($test_dir) as $file) { 
                
                if($file->isFile()) {
                    $pathname = $file->getPathname(); 
                    
                    if(is_readable($pathname)) {
                        require $pathname;

                        $filename = $file->getFileName();
                        $class = "\\Test\Controllers\\" . substr($filename, 0, strlen($filename) - 4);
                        $class::test();
                    }
                }
            }

            return Command::SUCCESS;

        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function configure() : void
    {
        $this->setHelp('Run controllers tests');
        $this->setDefinition([
            new InputOption('controller', null, InputOption::VALUE_REQUIRED, 'Run a specific controller test.')
        ]);
    }
}
