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
    protected $root_path;

    /**
     * Set root path
     * 
     * @param string $path
     * @return void
     */
    public function setPath(string $path) : void
    {
        $this->root_path = $path;
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
            $this->root_path . '/storage/framework/sessions',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $filter = new \Clicalmani\Foundation\Misc\RecursiveFilter($iterator);
        $filter->setPattern('^sess');

        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            unlink($file->getRealPath());
        }

        $this->output->writeln('Session cleared');
    }
}