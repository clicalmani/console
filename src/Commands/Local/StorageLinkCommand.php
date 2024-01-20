<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create symbolic link
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'storage:link',
    description: 'Create a symbolic link of storage into the public directory.',
    hidden: false
)]
class StorageLinkCommand extends Command
{
    private $storage_path, $public_path;

    public function __construct(protected $root_path)
    {
        $this->storage_path = $this->root_path . '/storage';
        $this->public_path = $this->root_path . '/public';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if (FALSE !== symlink($this->storage_path, $this->public_path)) return Command::SUCCESS;
        return Command::FAILURE;
    }
}
