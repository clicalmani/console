<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Routes\Route;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    public function __construct(protected $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct($root_path);

        /**
         * Init routing
         */
        \Clicalmani\Routes\Route::init();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ( $input->getOption('api') && ! defined('CONSOLE_API_ROUTE')) {
            define('CONSOLE_API_ROUTE', true);
        }

        /**
         * Provide route service
         */
        with( new \App\Providers\RouteServiceProvider )->boot();
        
        print_r(Route::getSignatures());
        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('List all registered routes');
        $this->setDefinition([
            new InputOption('api', null, InputOption::VALUE_NONE, 'Show API routes')
        ]);
    }
}
