<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'db:drop',
    description: 'Drop a signle entity.',
    hidden: false
)]
class DropEntityCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $model = $input->getArgument('model');
        $class = "\\App\\Models\\$model";
        
        try {
            /** @var \Clicalmani\Database\Factory\Models\Elegant */
            $model = new $class;
            $entity = $model->getEntity();
            $entity->setModel($model);

            $entity->drop();

        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Drop a single entity');
        $this->setDefinition([
            new InputArgument('model', InputArgument::REQUIRED, 'Entity model name')
        ]);
    }
}
