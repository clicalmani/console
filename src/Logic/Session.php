<?php
namespace Clicalmani\Console\Logic;

class Session
{
    /**
     * Output
     * 
     * @var mixed
     */
    protected $output;

    /**
     * Root path
     * 
     * @var string
     */
    protected $rootPath;

    /**
     * Set root path
     * 
     * @param string $path
     * @return void
     */
    public function setPath(string $path) : void
    {
        $this->rootPath = $path;
    }

    /**
     * Set output
     * 
     * @param mixed $output
     * @return void
     */
    public function setOutput(mixed $output) : void
    {
        $this->output = $output;
    }

    /**
     * Clear session
     * 
     * @return void
     */
    public function clear() : void
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->rootPath . '/storage/framework/sessions',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $filter = new \Clicalmani\Foundation\Filesystem\RecursiveFilter($iterator);
        $filter->setPattern('^sess');

        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            unlink($file->getRealPath());
        }

        $this->output->writeln('Session cleared');
    }
}