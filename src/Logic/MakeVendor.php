<?php
namespace Clicalmani\Console\Logic;

use Clicalmani\Core\Sandbox\Sandbox;

class MakeVendor
{
    /**
     * Input
     * 
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * Output
     * 
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Sample file path
     * 
     * @var string
     */
    private $sampleDir;

    public function __construct(string $dir)
    {
        $this->sampleDir = $dir;
    }

    /**
     * Set output
     * 
     * @param mixed $output
     * @return void
     */
    public function setInput(mixed $input) : void
    {
        $this->input = $input;
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
     * @return bool
     */
    public function isMetrics() : bool
    {
        return $this->input->getArgument('name') === 'metrics';
    }

    /**
     * @return bool
     */
    public function isQueue() : bool
    {
        return $this->input->getArgument('name') === 'queue';
    }

    /**
     * Return sample file
     * 
     * @return string
     */
    public function getSample() : string
    {
        if ($this->isMetrics()) {
            return 'metrics.config';
        }

        if ($this->isQueue()) {
            return 'queue.config';
        }

        return '';
    }

    /**
     * Create a new controller
     * 
     * @return bool
     */
    public function create() : bool
    {
        $filename = '';

        if ($this->isMetrics()) {
            $filename = config_path('/metrics.php');
            $this->output->writeln('Creating metrics config ...');
        }

        if ($this->isQueue()) {
            $filename = config_path('/queue.php');
            $this->output->writeln('Creating queue config ...');
        }

        return file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( $this->sampleDir . "/{$this->getSample()}"), []) )
        );
    }
}