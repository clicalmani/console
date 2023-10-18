<?php 
namespace Clicalmani\Console\Commands;

use Symfony\Component\Console\Command\Command as ConsoleCommand;

abstract class Command extends ConsoleCommand
{
    /**
     * Base path
     * 
     * @var string
     */
    protected $root_path;

    public function __construct(string $root_path = null)
    {
        $this->root_path = $root_path;
        parent::__construct();
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
}
