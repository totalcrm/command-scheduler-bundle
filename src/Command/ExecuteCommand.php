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
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use TotalCRM\CommandScheduler\Entity\Repository\ScheduledCommandRepository;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use TotalCRM\CommandScheduler\Entity\ScheduledHistory;

/**
 * Class ExecuteCommand
 * @package TotalCRM\CommandScheduler\Command
 * Start bin/console scheduler:execute
 */
class ExecuteCommand extends Command
{
    use LockableTrait;

    /**
     * @var EntityManager|EntityManagerInterface
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
     *
     * @throws Exception|MappingException|OptimisticLockException|TransactionRequiredException|\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->lock('totalcrm:scheduler:execute');

        $output->writeln('<info>Start : '.($this->dumpMode ? 'Dump' : 'Execute').' all scheduled command</info>');

        // Before continue, we check that the output file is valid and writable (except for gaufrette)
        if (false !== $this->logPath && 0 !== strpos($this->logPath, 'gaufrette:') && false === is_writable($this->logPath)) {
            $output->writeln(
                '<error>'.$this->logPath.
                ' not found or not writable. You should override `log_path` in your config.yml'.'</error>'
            );
            $this->release();

            return Command::FAILURE;
        }

        /** @var ScheduledCommandRepository $commandRepository */
        $commandRepository = $this->em->getRepository(ScheduledCommand::class);
        /** @var ScheduledCommand[] $commands */
        $commands = $commandRepository->findEnabledCommand();

        $noneExecution = true;
        /** @var ScheduledCommand $command */
        foreach ($commands as $command) {

            $command = $commandRepository->find($command->getId());

            if ($command->isDisabled() || $command->isLocked()) {
                continue;
            }

            /** @var CronExpression $cron */
            $cron = new CronExpression($command->getCronExpression());
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
            } else if ($nextRunDate < $now) {
                $noneExecution = false;
                $output->writeln(
                    'Command <comment>'.$command->getCommand().
                    '</comment> should be executed - last execution : <comment>'.
                    $command->getLastExecution()->format(DateTimeInterface::ATOM).'.</comment>'
                );

                if (!$input->getOption('dump')) {
                    $this->executeCommand($command, $output, $input);
                }
            }
        }

        if (true === $noneExecution) {
            $output->writeln('Nothing to do.');
        }

        $this->release();

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
        /** @var ScheduledCommandRepository $commandRepository */
        $commandRepository = $this->em->getRepository(ScheduledCommand::class);

        $this->em->getConnection()->beginTransaction();
        
        try {
            /** @var ScheduledCommand $notLockedCommand */
            $notLockedCommand = $commandRepository->getNotLockedCommand($scheduledCommand->getId());

            if ($notLockedCommand instanceof ScheduledCommand) {
                throw new \Exception();
            }

            /** @var ScheduledCommand $scheduledCommand */
            $scheduledCommand = $commandRepository->find($scheduledCommand->getId());
            $scheduledCommand->setLastExecution(new DateTime());
            $scheduledCommand->setLocked(true);

            $this->em->getConnection()->commit();

            $this->em->persist($scheduledCommand);
            $this->em->flush();

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

        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = $commandRepository->find($scheduledCommand->getId());
        $scheduledHistory = null;

        if ($scheduledCommand->isHistory()) {
            $scheduledHistory = new ScheduledHistory();
            $scheduledHistory
                ->setDateExecution(new DateTime())
                ->setCommandId($scheduledCommand->getId())
            ;
            $this->em->persist($scheduledHistory);
        }

        try {
            /** @var Command $command */
            $command = $this->getApplication()->find($scheduledCommand->getCommand());
        } catch (InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            $output->writeln('<error>Cannot find '.$scheduledCommand->getCommand().'</error>');

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
                    $this->logPath.$scheduledCommand->getLogFile(),
                    'a',
                    false
                ), $this->commandsVerbosity
            );
        } else {
            /** @var NullOutput $logStreamOutput */
            $logOutput = new NullOutput();
        }

        try {
            $output->writeln('<info>Execute</info> : <comment>'.$scheduledCommand->getCommand() . ' ' . $scheduledCommand->getArguments() . '</comment>');
            $result = $command->run($input, $logBufferedOutput);
        } catch (\Exception $e) {
            $logBufferedOutput->writeln($e->getMessage());
            $logBufferedOutput->writeln($e->getTraceAsString());
            $result = -1;
        }

        if (false === $this->em->isOpen()) {
            $output->writeln('<comment>Entity manager closed by the last command.</comment>');
            $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
        }

        $messages = $logBufferedOutput->fetch();
        $logOutput->write($messages);

        $scheduledCommand
            ->setLastMessages($messages)
            ->setLastReturnCode((int)$result)
            ->setExecuteImmediately(false)
            ->setLocked(false)
        ;
        $this->em->persist($scheduledCommand);

        if ($scheduledCommand->isHistory() && $scheduledHistory instanceof ScheduledHistory) {
            $scheduledHistory
                ->setDateExecution(new DateTime())
                ->setReturnCode($result)
                ->setMessages($messages)
            ;
            $this->em->persist($scheduledHistory);
        }

        $this->em->flush();

        try {
            $this->em->clear();
        } catch (MappingException $e) {
        }

        unset($command);
        gc_collect_cycles();
    }
}
