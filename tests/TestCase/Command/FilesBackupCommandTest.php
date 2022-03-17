<?php
declare(strict_types=1);

/**
 * This file is part of php-files-backup.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/php-files-backup
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace FilesBackup\Test\TestCase\Command;

use FilesBackup\Command\FilesBackupCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tools\TestSuite\TestCase;

/**
 * FilesBackupCommandTest class
 */
class FilesBackupCommandTest extends TestCase
{
    /**
     * Internal method to get a `CommandTest` instance for the tested command
     * @return CommandTester
     */
    protected function getCommandTester(): CommandTester
    {
        $command = new FilesBackupCommand();

        return new CommandTester($command);
    }

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $target = TMP . 'tmp_' . mt_rand() . '.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'));
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Source: ' . APP, $output);
        $this->assertStringContainsString('Target: ' . $target, $output);
        $this->assertStringContainsString('Backup exported successfully to `' . $target . '`', $output);
        @unlink($target);

        $commandTester->execute(compact('target') + ['--git-ignore' => true]);

        $output = $commandTester->getDisplay();
        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('The files and directories specified in the `.git_ignore` file are automatically ignored');
    }

    /**
     * Test for `execute()` method, with failure
     * @test
     */
    public function testExecuteWithFailure(): void
    {
        $target = TMP . 'noExisting' . DS . 'file.zip';

        $commandTester = $this->getCommandTester();
        $commandTester->execute(compact('target'));
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error: file or directory `' . dirname($target) . '` does not exist', $output);
    }
}
