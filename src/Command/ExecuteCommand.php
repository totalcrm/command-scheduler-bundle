<?php

namespace TotalCRM\CommandScheduler\Command;

use Cron\CronExpression;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Doctrine\Persistence\Mapping\MappingException;
use InvalidArgumentException;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use TotalCRM\CommandScheduler\Entity\Repository\ScheduledCommandRepository;
use TotalCRM\CommandScheduler\Entity\Repository\ScheduledHistoryRepository;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use TotalCRM\CommandScheduler\Entity\ScheduledHistory;

/**
 * Class ExecuteCommand
 * @package TotalCRM\CommandScheduler\Command
 * Start bin/console scheduler:execute
 */
class ExecuteCommand extends Command
{
    /** @var EntityManager|EntityManagerInterface */
    private $em;
    private string $logPath;
    private bool $dumpMode;
    private int $commandsVerbosity;

    /**
     * ExecuteCommand constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string|null $managerName
     * @param string|null $logPath
     */
    public function __construct(ManagerRegistry $managerRegistry, ?string $managerName = null, ?string $logPath = null)
    {
        parent::__construct();
        
        $this->em = $managerRegistry->getManager($managerName);
        $this->logPath = $logPath;
        if (false !== $this->logPath) {
            $this->logPath = rtrim($this->logPath, '/\\').DIRECTORY_SEPARATOR;
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('scheduler:execute')
            ->setDescription('Execute scheduled commands')
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Display next execution')
            ->addOption('no-output', null, InputOption::VALUE_NONE, 'Disable output message from scheduler')
            ->setHelp('This class is the entry point to execute all scheduled command')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->dumpMode = $input->getOption('dump');
        $this->commandsVerbosity = $output->getVerbosity();

        if (true === $input->getOption('no-output')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception|MappingException|OptimisticLockException|TransactionRequiredException|\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln(date('Y-m-d H:i:s') . ' <info>Start</info>: ' . ($this->dumpMode ? 'Dump' : 'Execute') . ' all scheduled command');

        if (false !== $this->logPath && 0 !== strpos($this->logPath, 'gaufrette:') && false === is_writable($this->logPath)) {
            $output->writeln(date('Y-m-d H:i:s') . ' <error>' . $this->logPath . ' not found or not writable. You should override `log_path` in your config.yml</error>');

            return Command::FAILURE;
        }

        /** @var ScheduledCommandRepository $commandRepository */
        $commandRepository = $this->em->getRepository(ScheduledCommand::class);
        /** @var ScheduledCommand[] $commands */
        $commands = $commandRepository->findEnabledCommand();

        $countExecution = 0;
        /** @var ScheduledCommand $command */
        foreach ($commands as $command) {

            $command = $commandRepository->find($command->getId());

            if ($command->isDisabled()) {
                continue;
            }

            $now = new DateTime();
            try {
                /** @var CronExpression $cron */
                $cron = new CronExpression($command->getCronExpression());
                $nextRunDate = $cron->getNextRunDate($command->getLastStart());
            } catch (\Exception $e) {
                $output->writeln(date('Y-m-d H:i:s') . ' <info>Error</info>: <comment>' . $command->getId() . '. ' . $command->getCommand() . '</comment> <error>' . trim($e->getMessage()). '</error>');
                $nextRunDate = $now;
            }

            if ($command->isExecuteImmediately()) {
                ++$countExecution;
                $output->writeln(date('Y-m-d H:i:s') . ' <info>Immediately execution</info>: <comment>' . $command->getId() . '. ' . $command->getCommand() . '</comment>');

                if (!$input->getOption('dump')) {
                    $this->executeCommand($command, $output, $input);
                }
            } else if ($nextRunDate < $now) {
                ++$countExecution;
                $output->writeln(date('Y-m-d H:i:s') . ' <info>Command</info>: <comment>' . $command->getId() . '. ' . $command->getCommand() . '</comment> should be executed - last execution : <comment>' . $command->getLastStart()->format(DateTimeInterface::ATOM) . '.</comment>'
                );

                if (!$input->getOption('dump')) {
                    $this->executeCommand($command, $output, $input);
                }
            }
        }

        if (!$countExecution) {
            $output->writeln(date('Y-m-d H:i:s') . ' <info>Stop</info>: Nothing execute commands');
        } else {
            $output->writeln(date('Y-m-d H:i:s') . ' <info>Stop</info>: Executed commands count - <info>' . $countExecution . '</info>');
        }

        return Command::SUCCESS;
    }

    /**
     * @param ScheduledCommand $scheduledCommand
     * @param OutputInterface $output
     * @param InputInterface $input
     * @throws Exception
     * @throws ORMException
     */
    private function executeCommand(ScheduledCommand $scheduledCommand, OutputInterface $output, InputInterface $input)
    {
        $scheduledCommandId = $scheduledCommand->getId();
        $scheduledHistoryId = null;

        /** @var ScheduledCommandRepository $commandRepository */
        $commandRepository = $this->em->getRepository(ScheduledCommand::class);
        /** @var ScheduledHistoryRepository $scheduledHistoryRepository */
        $scheduledHistoryRepository = $this->em->getRepository(ScheduledHistory::class);

        $this->em->getConnection()->beginTransaction();
        
        try {
            /** @var ScheduledCommand $scheduledCommand */
            $scheduledCommand = $commandRepository->find($scheduledCommandId);
            if (!$scheduledCommand instanceof ScheduledCommand) {
                return;
            }

            $scheduledCommand
                ->setLastStart(new DateTime())
                ->setLastFinish(null)
                ->setExecuteImmediately(false);

            $this->em->persist($scheduledCommand);
            $this->em->flush();
            $this->em->getConnection()->commit();
            
        } catch (\Exception $e) {

            $this->em->getConnection()->rollBack();
            $output->writeln(
                sprintf(
                    date('Y-m-d H:i:s') . ' <error>Command %s is locked %s</error>',
                    $scheduledCommand->getCommand(),
                    (!empty($e->getMessage()) ? sprintf('(%s)', $e->getMessage()) : '')
                )
            );

            return;
        }

        /** @var ScheduledCommand|null $scheduledCommand */
        $scheduledCommand = $commandRepository->find($scheduledCommandId);

        if (!$scheduledCommand instanceof ScheduledCommand) {
            return;
        }

        if ($scheduledCommand->isHistory()) {
            $scheduledHistory = new ScheduledHistory();
            $scheduledHistory
                ->setDateStart(new DateTime())
                ->setCommandId($scheduledCommandId);
            $this->em->persist($scheduledHistory);
            $this->em->flush();
            $scheduledHistoryId = $scheduledHistory->getId();
        }

        try {
            /** @var Command $command */
            $command = $this->getApplication()->find($scheduledCommand->getCommand());

        } catch (InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            $this->em->persist($scheduledCommand);
            $this->em->flush();

            $output->writeln(date('Y-m-d H:i:s') . ' <error>Cannot find '.$scheduledCommand->getCommand().'</error>');

            return;
        }

        $input = new StringInput(
            $scheduledCommand->getCommand() . ' ' . $scheduledCommand->getArguments() . ' --env=' . $input->getOption('env')
        );

        $command->mergeApplicationDefinition();
        $input->bind($command->getDefinition());

        if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
            $input->setInteractive(false);
        }

        /** @var BufferedOutput $logBufferedOutput */
        $logBufferedOutput = new BufferedOutput();

        if ($this->logPath && $scheduledCommand->getLogFile()) {
            /** @var StreamOutput $logStreamOutput */
            $logOutput = new StreamOutput(
                fopen(
                    $this->logPath . $scheduledCommand->getLogFile(),
                    'a',
                    false
                ), $this->commandsVerbosity
            );
        } else {
            /** @var NullOutput $logStreamOutput */
            $logOutput = new NullOutput();
        }

        try {
            $output->writeln(date('Y-m-d H:i:s') . ' <info>Execute</info>: <comment>' . $scheduledCommand->getId() . '. ' . $scheduledCommand->getCommand() . ' ' . $scheduledCommand->getArguments() . '</comment>');
            $result = $command->run($input, $logBufferedOutput);

        } catch (\Exception $e) {

            $logBufferedOutput->writeln($e->getMessage());
            $logBufferedOutput->writeln($e->getTraceAsString());

            $result = Command::INVALID;
        }

        if (false === $this->em->isOpen()) {
            $output->writeln(date('Y-m-d H:i:s') . ' <comment>Entity manager closed by the last command.</comment>');
            $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
        }

        $messages = $logBufferedOutput->fetch();
        $logOutput->write($messages);

        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = $commandRepository->find($scheduledCommandId);
        if ($scheduledCommand instanceof ScheduledCommand) {
            $scheduledCommand
                ->setLastMessages($messages)
                ->setLastFinish(new DateTime())
                ->setLastReturnCode((int)$result)
                ->setLocked(false);
            $this->em->persist($scheduledCommand);

            if ($scheduledCommand->isHistory() && $scheduledHistoryId) {
                /** @var ScheduledHistory $scheduledHistory */
                $scheduledHistory = $scheduledHistoryRepository->find($scheduledHistoryId);
                if ($scheduledHistory instanceof ScheduledHistory) {
                    $scheduledHistory
                        ->setDateFinish(new DateTime())
                        ->setReturnCode($result)
                        ->setMessages($messages);
                    $this->em->persist($scheduledHistory);
                }
            }

            $this->em->flush();
        }

        try {
            $this->em->clear();
        } catch (MappingException $e) {
        }

        unset($command);
        gc_collect_cycles();
    }
}
