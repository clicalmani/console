<?php
namespace Clicalmani\Console\Logic;

use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * This file is part of the tonka project.
 * 
 * 
 * This file contains the MakeController class which is responsible for 
 * handling the logic related to creating new controllers within the 
 * application.
 * 
 * @package Tonka
 * @subpackage Logic
 * @version 4.0
 * @since 2023
 */
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
     * Check if singleton controller
     * 
     * @return bool
     */
    public function isSingleton() : bool
    {
        return !!$this->input->getOption('singleton');
    }

    /**
     * Check if resource has model.
     * 
     * @return bool
     */
    public function hasModel() : bool
    {
        return !!$this->input->getOption('model');
    }

    /**
     * Get model resource
     * 
     * @return string|null
     */
    public function getResourceModel() : string|null
    {
        if ($resource = $this->input->getOption('model')) return "App\\Models\\$resource";

        return null;
    }

    /**
     * Get resource model
     * 
     * @return string|null
     */
    public function getResourceParam() : string|null
    {
        if ($this->isResource()) {
            if ($resource = $this->input->getOption('model')) return strtolower($resource);
        }

        return null;
    }

    /**
     * Return sample file
     * 
     * @return string
     */
    public function getSample() : string
    {
        $sample = '';
        
        if ($this->isResource()) {

            $sample = 'ControllerResource';

            if ($this->isApi()) {
                $sample .= 'Api';
            }

            if ($this->hasModel()) {
                $sample .= 'WithModel';
            }
        } elseif ($this->isInvokable()) {

            $sample = 'ControllerInvokable';

            if ($this->isApi()) {
                $sample .= 'Api';
            }
        } elseif ($this->isSingleton()) {

            $sample = 'ControllerSingleton';

            if ($this->isApi()) {
                $sample .= 'Api';
            }
        }

        if ( empty($sample) ) {

            $sample = 'Controller';

            if ($this->isApi()) {
                $sample .= 'Api';
            }
        }

        return "$sample.sample";
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
                'model_class' => $this->getResourceModel(),
                'parameter'   => $this->getResourceParam()
            ]) )
        );
    }

    /**
     * Maybe generate form requests
     * 
     * @param string $requests_path
     * @return void
     */
    public function maybeGenerateFormRequests(string $requests_path) : void
    {
        if ($this->input->getOption('request') AND $resource = $this->input->getOption('model')) {

            $requests = ['Store', 'Update'];

            foreach ($requests as $name) {
                file_put_contents(
                    "$requests_path//{$name}{$resource}Request.php", 
                    ltrim( Sandbox::eval(file_get_contents( $this->sampleDir . "/Samples/ResourceRequest.sample"), [
                        'request'  => "{$name}{$resource}Request",
                        'namespace' => "App\\Http\\Requests"
                    ]) )
                );
            }
        }
    }
}
