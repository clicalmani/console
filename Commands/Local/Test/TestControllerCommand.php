<?php
namespace Clicalmani\Console\Commands\Local\Test;

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
    name: 'test:controller',
    description: 'Test a controller',
    hidden: false
)]
class TestControllerCommand extends Command
{
    private $controllers_path;

    public function __construct(private $root_path)
    {
        $this->controllers_path = $this->root_path . '/app/test/controllers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ($controller = $input->getOption('controller')) {
            try {
                require $this->controllers_path . "//{$controller}Test.php";

                $class = "\\App\Test\\Controllers\\{$controller}Test";
                $class::test();

                return Command::SUCCESS;

            } catch(\Exception $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }
        }

        try {
            $test_dir = new \RecursiveDirectoryIterator($this->controllers_path);
	
            foreach (new \RecursiveIteratorIterator($test_dir) as $file) { 
                
                if($file->isFile()) {
                    $pathname = $file->getPathname(); 
                    
                    if(is_readable($pathname)) {
                        require $pathname;

                        $filename = $file->getFileName();
                        $class = "\\App\Test\Controllers\\" . substr($filename, 0, strlen($filename) - 4);
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
