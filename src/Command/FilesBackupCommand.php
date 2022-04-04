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
namespace FilesBackup\Command;

use FilesBackup\Command\FilesBackupCommandSubscriber;
use FilesBackup\FilesBackup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FilesBackupCommand class
 */
class FilesBackupCommand extends Command
{
    /**
     * Name of the command
     * @var string
     */
    protected static $defaultName = 'backup';

    /**
     * Description of the command
     * @var string
     */
    protected static $defaultDescription = 'Performs a files backup';

    /**
     * Command configuration
     * @return void
     */
    protected function configure(): void
    {
        $this->setHelp('This command performs a files backup')
            ->addArgument('target', InputArgument::REQUIRED, 'Target zip file you want to create')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source directory', defined('APP') ? APP : (defined('ROOT') ? ROOT : getcwd()))
            ->addOption('exclude', 'e', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Excludes directories from matching. Directories must be relative')
            ->addOption('git-ignore', null, InputOption::VALUE_NONE, 'Automatically ignores the files and directories specified in the `.gitignore` file');
    }

    /**
     * Command execution
     * @param \Symfony\Component\Console\Input\InputInterface $input InputInterface
     * @param \Symfony\Component\Console\Output\OutputInterface $output OutputInterface
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Gets and writes target and sources
        $target = $input->getArgument('target');
        $source = $input->getOption('source');
        $output->writeln('Source: `' . $source . '`');
        $output->writeln('Target: `' . $target . '`');

        //Sets `exclude` option
        if ($input->getOption('exclude')) {
            $options['exclude'] = $input->getOption('exclude');
            $excludedDirs = implode(', ', array_map(function (string $dir): string {
                return '`' . $dir . '`';
            }, (array)$input->getOption('exclude')));
            $output->writeln('Excluded directories: ' . $excludedDirs);
        }
        //Sets `git-ignore` option
        if ($input->getOption('git-ignore')) {
            $options['git_ignore'] = true;
            $output->writeln('The files and directories specified in the `.git_ignore` file are automatically ignored');
        }

        try {
            $FilesBackup = new FilesBackup($source, $options ?? []);
            $FilesBackup->getEventDispatcher()->addSubscriber(new FilesBackupCommandSubscriber($output));
            $FilesBackup->create($target);

            $output->writeln('<info>Backup exported successfully to: `' . $target . '`</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . lcfirst($e->getMessage()) . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
