<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Routes\Route;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show all registered routes
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'route:list',
    description: 'Show all registered routes',
    hidden: false
)]
class RouteCommand extends Command
{
    private $database_path;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        print_r(Route::all());
        return Command::SUCCESS;
    }
}
