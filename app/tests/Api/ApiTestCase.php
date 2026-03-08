<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApiTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        self::bootKernel();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $commands = [
            [
                'command' => 'doctrine:database:drop',
                '--force' => true,
                '--if-exists' => true,
                '--env' => 'test',
            ],
            [
                'command' => 'doctrine:database:create',
                '--env' => 'test',
            ],
            [
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
                '--env' => 'test',
            ],
        ];

        foreach ($commands as $command) {
            $application->run(new ArrayInput($command), new NullOutput());
        }

        self::ensureKernelShutdown();
    }
}
