<?php

namespace TotalCRM\CommandScheduler\Command;

use Cron\CronExpression;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\Console\Output\BufferedOutput;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use TotalCRM\CommandScheduler\Entity\ScheduledHistory;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class ExecuteCommand
 * @package TotalCRM\CommandScheduler\Command
 */
class ExecuteCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $logPath;

    /**
     * @var bool
     */
    private $dumpMode;

    /**
     * @var int
     */
    private $commandsVerbosity;

    /**
     * ExecuteCommand constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param $managerName
     * @param $logPath
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName, $logPath)
    {
        $this->em = $managerRegistry->getManager($managerName);
        $this->logPath = $logPath;

        // If logpath is not set to false, append the directory separator to it
        if (false !== $this->logPath) {
            $this->logPath = rtrim($this->logPath, '/\\').DIRECTORY_SEPARATOR;
        }

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
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
     * Initialize parameters and services used in execute function.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->dumpMode = $input->getOption('dump');

        // Store the original verbosity before apply the quiet parameter
        $this->commandsVerbosity = $output->getVerbosity();

        if (true === $input->getOption('no-output')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start : '.($this->dumpMode ? 'Dump' : 'Execute').' all scheduled command</info>');

        // Before continue, we check that the output file is valid and writable (except for gaufrette)
        if (false !== $this->logPath && 0 !== strpos($this->logPath, 'gaufrette:') && false === is_writable(
                $this->logPath
            )
        ) {
            $output->writeln(
                '<error>'.$this->logPath.
                ' not found or not writable. You should override `log_path` in your config.yml'.'</error>'
            );

            return 1;
        }

        /** @var ScheduledCommand[] $commands */
        $commands = $this->em->getRepository(ScheduledCommand::class)->findEnabledCommand();

        $noneExecution = true;
        /** @var ScheduledCommand $command */
        foreach ($commands as $command) {
            $this->em->refresh($this->em->find(ScheduledCommand::class, $command));
            if ($command->isDisabled() || $command->isLocked()) {
                continue;
            }

            /** @var CronExpression $cron */
            $cron = CronExpression::factory($command->getCronExpression());
            $nextRunDate = $cron->getNextRunDate($command->getLastExecution());
            $now = new DateTime();

            if ($command->isExecuteImmediately()) {
                $noneExecution = false;
                $output->writeln(
                    'Immediately execution asked for : <comment>'.$command->getCommand().'</comment>'
                );

                if (!$input->getOption('dump')) {
                    $this->executeCommand($command, $output, $input);
                }
            } elseif ($nextRunDate < $now) {
                $noneExecution = false;
                $output->writeln(
                    'Command <comment>'.$command->getCommand().
                    '</comment> should be executed - last execution : <comment>'.
                    $command->getLastExecution()->format(\DateTimeInterface::ATOM).'.</comment>'
                );

                if (!$input->getOption('dump')) {
                    $this->executeCommand($command, $output, $input);
                }
            }
        }

        if (true === $noneExecution) {
            $output->writeln('Nothing to do.');
        }

        return 0;
    }

    /**
     * @param ScheduledCommand $scheduledCommand
     * @param OutputInterface $output
     * @param InputInterface $input
     * @throws ORMException|OptimisticLockException|TransactionRequiredException|Exception|MappingException
     */
    private function executeCommand(ScheduledCommand $scheduledCommand, OutputInterface $output, InputInterface $input)
    {
        //reload command from database before every execution to avoid parallel execution
        $this->em->getConnection()->beginTransaction();
        
        try {
            /** @var ScheduledCommand $notLockedCommand */
            $notLockedCommand = $this->em->getRepository(ScheduledCommand::class)->getNotLockedCommand($scheduledCommand);

            if (null === $notLockedCommand) {
                throw new \Exception();
            }

            $scheduledCommand = $notLockedCommand;
            $scheduledCommand->setLastExecution(new DateTime());
            $scheduledCommand->setLocked(true);

            $this->em->persist($scheduledCommand);
            $this->em->flush();
            $this->em->getConnection()->commit();

        } catch (\Exception $e) {

            $this->em->getConnection()->rollBack();
            $output->writeln(
                sprintf(
                    '<error>Command %s is locked %s</error>',
                    $scheduledCommand->getCommand(),
                    (!empty($e->getMessage()) ? sprintf('(%s)', $e->getMessage()) : '')
                )
            );

            return;
        }

        $scheduledCommand = $this->em->find(ScheduledCommand::class, $scheduledCommand);

        try {
            /** @var Command $command */
            $command = $this->getApplication()->find($scheduledCommand->getCommand());
        } catch (\InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            $output->writeln('<error>Cannot find '.$scheduledCommand->getCommand().'</error>');

            return;
        }

        $input = new StringInput(
            $scheduledCommand->getCommand().' '.$scheduledCommand->getArguments().' --env='.$input->getOption('env')
        );

        $command->mergeApplicationDefinition();
        $input->bind($command->getDefinition());

        // Disable interactive mode if the current command has no-interaction flag
        if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
            $input->setInteractive(false);
        }

        // Use a StreamOutput or NullOutput to redirect write() and writeln() in a log file
        if (false === $this->logPath || empty($scheduledCommand->getLogFile())) {
            /** @var BufferedOutput $logOutput */
            $logOutput = new BufferedOutput();
        } else {
            /** @var StreamOutput $logOutput */
            $logOutput = new StreamOutput(
                fopen(
                    $this->logPath.$scheduledCommand->getLogFile(),
                    'a',
                    false
                ), $this->commandsVerbosity
            );
        }
        
        // Execute command and get return code
        try {
            $output->writeln('<info>Execute</info> : <comment>'.$scheduledCommand->getCommand() . ' ' . $scheduledCommand->getArguments() . '</comment>');
            $result = $command->run($input, $logOutput);
        } catch (\Exception $e) {
            $logOutput->writeln($e->getMessage());
            $logOutput->writeln($e->getTraceAsString());
            $result = -1;
        }

        if (false === $this->em->isOpen()) {
            $output->writeln('<comment>Entity manager closed by the last command.</comment>');
            $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
        }

        $scheduledCommand
            ->setLastReturnCode($result)
            ->setLocked(false)
            ->setExecuteImmediately(false)
        ;
        $this->em->persist($scheduledCommand);

        $messages = $logOutput->fetch();
        $scheduledHistory = new ScheduledHistory();
        $scheduledHistory
            ->setDateExecution(new DateTime())
            ->setCommandId($scheduledCommand->getId())
            ->setReturnCode($result)
            ->setMessages($messages)
        ;
        $this->em->persist($scheduledHistory);

        $this->em->flush();

        /*
         * This clear() is necessary to avoid conflict between commands and to be sure that none entity are managed
         * before entering in a new command
         */
        $this->em->clear();

        unset($command);
        gc_collect_cycles();
    }
}
