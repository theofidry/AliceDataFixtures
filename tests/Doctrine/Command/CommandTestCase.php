<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Doctrine\Command;

use Hautelook\AliceBundle\Tests\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class CommandTestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    protected function setUp()
    {
        self::bootKernel();

        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);

        $this->runConsole('doctrine:schema:drop', ['--force' => true]);
        $this->runConsole('doctrine:schema:create');
    }

    /**
     * Helper to run a Symfony command.
     *
     * @param string $command
     * @param array  $options
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function runConsole($command, array $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

    /**
     * @param string $expected
     * @param string $display
     */
    protected function assertFixturesDisplayEquals($expected, $display)
    {
        $expected = $this->normalizeFixturesDisplay($expected);
        $display = $this->normalizeFixturesDisplay($display);

        $this->assertCount(0, array_diff($expected, $display));
    }

    /**
     * @param string $display
     *
     * @return string[]
     */
    private function normalizeFixturesDisplay($display)
    {
        $display = trim($display, ' ');
        $display = trim($display, "\t");
        $display = preg_replace('/\n/', '', $display);
        $display = explode('  > loading ', $display);
        array_shift($display);

        return $display;
    }
}
