<?php 
namespace Clicalmani\Console\Commands;

use Clicalmani\Fundation\Container\SPL_Loader;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

if ( ! defined('CONSOLE_MODE_ACTIVE') ) {
    define('CONSOLE_MODE_ACTIVE', true);
}

abstract class Command extends ConsoleCommand
{
    protected $container;

    public function __construct(protected $root_path = null)
    {
        parent::__construct();
        
        $this->root_path = dirname( __DIR__, 5);

        \Clicalmani\Fundation\Providers\ServiceProvider::init(
            $app = require $this->root_path . '/config/app.php',
            $kernel = require $this->root_path . '/bootstrap/kernel.php',
            $http_kernel = require $this->root_path . '/app/Http/kernel.php'
        );
        
        $this->container = new SPL_Loader($root_path ?? $root_path);
    }

    /**
     * Check wether it's a path name
     * 
     * @param string $filename
     * @return mixed
     */
    protected function isPath(string $filename) : mixed
    {
        return strripos($filename, '\\');
    }

    /**
     * Retrieve the path name from the file name
     * 
     * @param string $filename
     * @return string
     */
    protected function getPath(string $filename) : string
    {
        return substr($filename, 0, (int)$this->isPath($filename));
    }

    /**
     * Retrieve the class name from the path name
     * 
     * @param string $filename
     * @return string
     */
    protected function getClass(string $filename) : string
    {
        return substr($filename, $this->isPath($filename) ? $this->isPath($filename) + 1: 0);
    }

    /**
     * Make a directory
     * 
     * @param string $pathname
     * @param int $mode
     * @param ?callable $callbak
     * @return void
     */
    protected function mkdir(string $pathname, int $mode = 0777, ?callable $callback = null) : void
    {
        if ( ! file_exists($pathname) ) {
            mkdir($pathname, $mode);

            if (null !== $callback) $callback();
        }
    }

    protected function formatOutput(string $message)
    {
        return str_pad("$message ", 100, '-');
    }
}
