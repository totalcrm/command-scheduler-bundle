<?php

namespace TotalCRM\CommandScheduler\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StartSchedulerCommand
 * @package TotalCRM\CommandScheduler\Command
 * Start bin/console scheduler:start
 */
class StartSchedulerCommand extends Command
{
    use LockableTrait;
    
    const PID_FILE = '.cron-pid';

    protected function configure()
    {
        $this->setName('scheduler:start')
            ->setDescription('Starts command scheduler')
            ->addOption('blocking', 'b', InputOption::VALUE_NONE, 'Run in blocking mode.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            
            return Command::SUCCESS;
        }

        if ($input->getOption('blocking')) {
            $output->writeln(sprintf('<info>%s</info>', 'Starting command scheduler in blocking mode.'));
            $this->schedulerExecute($output->isVerbose() ? $output : new NullOutput(), null);
            
            return Command::SUCCESS;
        }

        if (!extension_loaded('pcntl')) {
            throw new \RuntimeException('This command needs the pcntl extension to run.');
        }

        $pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.self::PID_FILE;

        if (-1 === $pid = pcntl_fork()) {
            throw new \RuntimeException('Unable to start the cron process.');
        } elseif (0 !== $pid) {
            if (false === file_put_contents($pidFile, $pid)) {
                throw new \RuntimeException('Unable to create process file.');
            }

            $output->writeln(sprintf('<info>%s</info>', 'Command scheduler started in non-blocking mode...'));

            return Command::SUCCESS;
        }

        if (-1 === posix_setsid()) {
            throw new \RuntimeException('Unable to set the child process as session leader.');
        }

        $this->schedulerExecute(new NullOutput(), $pidFile);

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param string $pidFile
     * @throws Exception
     */
    private function schedulerExecute(OutputInterface $output, ?string $pidFile = null)
    {
        $input = new ArrayInput([]);

        $console = $this->getApplication();
        $command = $console->find('scheduler:execute');

        while (true) {
            $now = microtime(true);
            usleep((60 - ($now % 60) + (int) $now - $now) * 1e6);

            if (null !== $pidFile && !file_exists($pidFile)) {
                break;
            }

            $command->run($input, $output);
        }
    }
}
