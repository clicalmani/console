<?php

namespace Clicalmani\Console\Test;

use Clicalmani\Console\Commands\Command as BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Clicalmani\Console\Test\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Override;
use Symfony\Component\Console\Input\InputOption;

/**
 * Consume
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'run',
    description: 'Test command',
    hidden: false
)]
class Command extends BaseCommand
{
    private Command $command;
    private CommandTester $commandTester;
    private string $testDir = 'test';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
        $this->testDir = $rootPath . '/test';
    }

    /**
     * Define available options
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setUp();

        $this->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Exécuter un test spécifique');
        $this->addOption('coverage', 'c', InputOption::VALUE_REQUIRED, 'Exécuter avec couverture de code');
        $this->addOption('display-deprecations', 'd', InputOption::VALUE_NONE, 'Display deprecations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $script = 'php vendor/bin/phpunit';

        if ($filter = $input->getOption('filter')) {
            $script .= " --filter $filter";
        }

        if ($coverage = $input->getOption('coverage')) {
            $script .= " --coverage $coverage";
        }

        if ($deprecations = $input->getOption('display-deprecations')) {
            $script = "php vendor/bin/phpunit --display-deprecations $deprecations";
        }

        exec($script, $out, $resultCode);

        if ($resultCode === 0) {
            $output->write($out, true);
            return Command::SUCCESS;
        }
        
        $output->write($out, true);

        return Command::FAILURE;
    }

    protected function setUp(): void
    {
        // Créer le répertoire de test si nécessaire
        $this->mkdir($this->testDir, 0777);
    }

    protected function tearDown(): void
    {
        // Nettoyer le répertoire de test
        if (is_dir($this->rootPath)) {
            rmdir($this->rootPath);
        }
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('test', $this->command->getName());
        $this->assertEquals('', $this->command->getDescription());
        $this->assertFalse($this->command->isHidden());
    }

    public function testDefaultTransportOption(): void
    {
        $this->commandTester->execute([]);
        
        $this->assertStringContainsString(
            'An error occurred while installing the crontab.',
            $this->commandTester->getDisplay()
        );
        
        $this->assertEquals(
            Command::FAILURE,
            $this->commandTester->getStatusCode()
        );
    }

    public function testCustomTransportOption(): void
    {
        $this->commandTester->execute([
            '--transport' => 'database',
        ]);
        
        $this->assertStringContainsString(
            'An error occurred while installing the crontab.',
            $this->commandTester->getDisplay()
        );
        
        $this->assertEquals(
            Command::FAILURE,
            $this->commandTester->getStatusCode()
        );
    }

    public function testShortTransportOption(): void
    {
        $this->commandTester->execute([
            '-t' => 'redis',
        ]);
        
        $this->assertStringContainsString(
            'An error occurred while installing the crontab.',
            $this->commandTester->getDisplay()
        );
        
        $this->assertEquals(
            Command::FAILURE,
            $this->commandTester->getStatusCode()
        );
    }

    public function testRootPathIsStored(): void
    {
        // Tester que le rootPath est correctement stocké
        $reflection = new \ReflectionClass($this->command);
        $property = $reflection->getProperty('rootPath');
        $property->setAccessible(true);
        
        $this->assertEquals(
            $this->rootPath,
            $property->getValue($this->command)
        );
    }

    public function testHelpOption(): void
    {
        $this->commandTester->execute([
            '--help' => true,
        ]);
        
        $display = $this->commandTester->getDisplay();
        
        // Vérifier que l'option transport apparaît dans l'aide
        $this->assertStringContainsString('--transport', $display);
        $this->assertStringContainsString('-t', $display);
        $this->assertStringContainsString('The transports to be used', $display);
    }

    public function testCommandRunsWithAllOptions(): void
    {
        $this->commandTester->execute([
            '--transport' => 'elegant',
            '--verbose' => true,
        ]);
        
        $this->assertStringContainsString(
            'An error occurred while installing the crontab.',
            $this->commandTester->getDisplay()
        );
        
        $this->assertEquals(
            Command::FAILURE,
            $this->commandTester->getStatusCode()
        );
    }

    public function testInvalidTransportOption(): void
    {
        // Tester avec une valeur vide pour transport
        $this->commandTester->execute([
            '--transport' => '',
        ]);
        
        $this->assertStringContainsString(
            'An error occurred while installing the crontab.',
            $this->commandTester->getDisplay()
        );
        
        $this->assertEquals(
            Command::FAILURE,
            $this->commandTester->getStatusCode()
        );
    }
}