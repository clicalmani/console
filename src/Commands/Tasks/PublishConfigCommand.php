<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PublishConfigCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'crunz:config',
    description: 'Create a copy of the configuration file.',
    hidden: false
)]
class PublishConfigCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $list_command = new ArrayInput([
            'command' => 'publish:config'
        ]);

        return $this->getCrunzApplication()->doRun($list_command, $output);
    }
}
