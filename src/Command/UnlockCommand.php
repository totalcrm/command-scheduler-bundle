<?php

namespace TotalCRM\CommandScheduler\Command;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\Console\Command\LockableTrait;
use TotalCRM\CommandScheduler\Entity\Repository\ScheduledCommandRepository;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnlockCommand
 * @package TotalCRM\CommandScheduler\Command
 */
class UnlockCommand extends Command
{
    use LockableTrait;
    
    private EntityManager $em;
    private int $defaultLockTimeout;
    private ?int $lockTimeout = null;
    private ?bool $unlockAll = false;
    private ?string $scheduledCommandName = '';

    /**
     * UnlockCommand constructor.
     * @param ManagerRegistry $managerRegistry
     * @param $managerName
     * @param $lockTimeout
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName, $lockTimeout)
    {
        parent::__construct();
        $this->em = $managerRegistry->getManager($managerName);
        $this->defaultLockTimeout = $lockTimeout;
    }

    protected function configure(): void
    {
        $this
            ->setName('scheduler:unlock')
            ->setDescription('Unlock one or all scheduled commands that have surpassed the lock timeout.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the command to unlock')
            ->addOption('all', 'A', InputOption::VALUE_NONE, 'Unlock all scheduled commands')
            ->addOption(
                'lock-timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Use this lock timeout value instead of the configured one (in seconds, optional)'
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->scheduledCommandName = $input->getArgument('name');
        $this->unlockAll = $input->getOption('all');
        $this->lockTimeout = $input->getOption('lock-timeout') ?: null;

        if (null === $this->lockTimeout) {
            $this->lockTimeout = $this->defaultLockTimeout;
        } else {
            if ('false' === $this->lockTimeout) {
                $this->lockTimeout = false;
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws OptimisticLockException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln(date('Y-m-d H:i:s') . ' <error>The command is already running in another process</error>');

            return Command::SUCCESS;
        }
        
        if (false === $this->unlockAll && null === $this->scheduledCommandName) {
            $output->writeln(date('Y-m-d H:i:s') . ' <error>Either the name of a scheduled command or the --all option must be set</error>');

            return Command::SUCCESS;
        }

        /** @var ScheduledCommandRepository $repository */
        $repository = $this->em->getRepository(ScheduledCommand::class);

        if (true === $this->unlockAll) {
            /** @var ScheduledCommand[] $failedCommands */
            $failedCommands = $repository->findLockedCommand();
            if (!$failedCommands) {
                $output->writeln(date('Y-m-d H:i:s') . ' <info>Nothing Scheduled Command lock</info>');

                return Command::SUCCESS;
            }
            foreach ($failedCommands as $failedCommand) {
                $this->unlock($failedCommand, $output);
            }
        } else {
            /** @var ScheduledCommand $scheduledCommand */
            $scheduledCommand = $repository->findOneBy(['name' => $this->scheduledCommandName, 'disabled' => false]);
            if (!$scheduledCommand instanceof ScheduledCommand) {
                $output->writeln(
                    sprintf(
                        date('Y-m-d H:i:s') . ' <error>Error: Scheduled Command with name "%s" not found or is disabled</error>',
                        $this->scheduledCommandName
                    )
                );

                return Command::SUCCESS;
            }
            $this->unlock($scheduledCommand, $output);
        }
        $this->em->flush();
        
        $this->release();

        return Command::SUCCESS;
    }

    /**
     * @param ScheduledCommand $command 
     * @param OutputInterface $output
     * @return bool|null
     * @throws Exception
     */
    protected function unlock(ScheduledCommand $command, OutputInterface $output): ?bool
    {
        if (false === $command->isLocked()) {
            $output->writeln(sprintf(date('Y-m-d H:i:s') . ' <info>Skipping: Scheduled Command "%s" is not locked</info>', $command->getName()));

            return false;
        }

        if (false !== $this->lockTimeout &&
            null !== $command->getLastExecution() &&
            $command->getLastExecution() >= (new DateTime())->sub(
                new DateInterval(sprintf('PT%dS', $this->lockTimeout))
            )
        ) {
            $output->writeln(
                sprintf(date('Y-m-d H:i:s') . ' <info>Skipping: Timout for scheduled Command "%s" has not run out</info>', $command->getName())
            );

            return false;
        }

        $command->setLocked(false);
        $this->em->persist($command);

        $output->writeln(sprintf(date('Y-m-d H:i:s') . ' <info>Scheduled Command "%s" has been unlocked</info>', $command->getName()));

        return true;
    }
}
