<?php
namespace Clicalmani\Console\Logic;

/**
 * GenerateAppKey Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
class GenerateAppKey
{
    private $env_file;
    private $key;

    public function __construct()
    {
        $this->env_file = root_path('/.env');
    }

    /**
     * Check if the app key is set
     * 
     * @return bool
     */
    public function isEnvSet() : bool
    {
        if ( file_exists($this->env_file) ) {
            $fh = \fopen($this->env_file, 'r');

            while (!feof($fh)) {
                $line = \fgets($fh, 1024);

                if (preg_match("/APP_KEY/", $line)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set the app key
     * 
     * @param string $key
     * @return string|null
     */
    public function generate() : string|null
    {
        $this->key = password( faker()->alphaNum(100) );

        if ( file_exists($this->env_file) ) {
            $fh = \fopen($this->env_file, 'r');

            while (!feof($fh)) {
                $line = \fgets($fh, 1024);

                if (preg_match("/APP_KEY/", $line)) {
                    file_put_contents(
                        $this->env_file,
                        str_replace(trim($line), "APP_KEY = $this->key", file_get_contents($this->env_file))
                    );

                    return $this->key;
                }
            }
        }

        return null;
    }

    /**
     * Get the app key
     * 
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }
}