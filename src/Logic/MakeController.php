<?php
namespace Clicalmani\Console\Logic;

use Clicalmani\Foundation\Sandbox\Sandbox;

class MakeController 
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
     * Check if API controller
     * 
     * @return bool
     */
    public function isApi() : bool
    {
        return !!$this->input->getOption('api');
    }

    /**
     * Check if invokable controller
     * 
     * @return bool
     */
    public function isInvokable() : bool
    {
        return !!$this->input->getOption('invokable');
    }

    /**
     * Check if resource controller
     * 
     * @return bool
     */
    public function isResource() : bool
    {
        return !!$this->input->getOption('resource');
    }

    /**
     * Get model resource
     * 
     * @return string|null
     */
    public function getResource() : string|null
    {
        if ($resource = $this->input->getOption('resource')) return "App\\Models\\$resource";

        if ($this->isResource()) $this->output->writeln('Resource name must be specified.');

        return null;
    }

    /**
     * Get resource parameter
     * 
     * @return string|null
     */
    public function getResourceParam() : string|null
    {
        if ($resource = $this->input->getOption('resource')) return strtolower($resource);

        return null;
    }

    /**
     * Return sample file
     * 
     * @return string
     */
    public function getSample() : string
    {
        $sample = 'Controller.sample';

        if ($this->isApi()) $sample = 'ControllerApi.sample';
        elseif ($this->isResource()) $sample = 'ControllerResource.sample';
        elseif ($this->isInvokable()) $sample = 'ControllerInvokable.sample';

        return $sample;
    }

    /**
     * Create a new controller
     * 
     * @param string $filename File name
     * @param string $namespace Namespace
     * @param string $class Class name
     * @param ?string $resource Resource name
     * @return bool
     */
    public function create(string $filename, string $namespace, string $class, ?string $resource = null) : bool
    {
        if ($this->isApi()) $this->output->writeln('Creating an API controller...');
        elseif ($this->isResource()) $this->output->writeln('Creating a resource controller...');
        elseif ($this->isInvokable()) $this->output->writeln('Creating an invokable controller...');

        return file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( $this->sampleDir . "/Samples/{$this->getSample()}"), [
                'controller'  => $class,
                'resource'    => $resource,
                'namespace'   => $namespace,
                'model_class' => $this->getResource(),
                'parameter'   => $this->getResourceParam()
            ]) )
        );
    }
}
