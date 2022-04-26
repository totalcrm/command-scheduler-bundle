<?php

namespace TotalCRM\CommandScheduler\Command;

use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TotalCRM\CommandScheduler\Entity\Repository\ScheduledCommandRepository;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;

/**
 * Class MonitorCommand
 * @package TotalCRM\CommandScheduler\Command
 * Start bin/console scheduler:monitor
 */
class MonitorCommand extends Command
{
    private EntityManager $em;
    private bool $dumpMode;
    private ?int $lockTimeout;
    /** @var string|array */
    private $receiver;
    private string $mailSubject;
    private bool $sendMailIfNoError;

    /**
     * MonitorCommand constructor.
     * @param ManagerRegistry $managerRegistry
     * @param $managerName
     * @param $lockTimeout
     * @param $receiver
     * @param $mailSubject
     * @param $sendMailIfNoError
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $managerName,
        $lockTimeout,
        $receiver,
        $mailSubject,
        $sendMailIfNoError
    ) {
        $this->em = $managerRegistry->getManager($managerName);
        $this->lockTimeout = $lockTimeout;
        $this->receiver = $receiver;
        $this->mailSubject = $mailSubject;
        $this->sendMailIfNoError = $sendMailIfNoError;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('scheduler:monitor')
            ->setDescription('Monitor scheduled commands')
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Display result instead of send mail')
            ->setHelp('This class is for monitoring all active commands.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dumpMode = $input->getOption('dump');
        
        if (!$this->dumpMode && 0 === count($this->receiver)) {
            $output->writeln('Please add receiver in configuration');
            
            return Command::FAILURE;
        }

        /** @var ScheduledCommandRepository $scheduledCommandRepository */
        $scheduledCommandRepository = $this->em->getRepository(ScheduledCommand::class);
        /** @var ScheduledCommand[] $failedCommands */
        $failedCommands = $scheduledCommandRepository->findFailedAndTimeoutCommands($this->lockTimeout);

        if (count($failedCommands) > 0) {
            $message = '';

            foreach ($failedCommands as $command) {
                $message .= sprintf(
                    "%s: returncode %s, locked: %s, last execution: %s\n",
                    $command->getName(),
                    $command->getLastReturnCode(),
                    $command->getLocked(),
                    $command->getLastStart()->format(DateTimeInterface::ATOM)
                );
            }

            if ($this->dumpMode) {
                $output->writeln($message);
            } else {
                $this->sendMails($message);
            }
        } else {
            if ($this->dumpMode) {
                $output->writeln('No errors found.');
            } elseif ($this->sendMailIfNoError) {
                $this->sendMails('No errors found.');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $message message to be sent
     */
    private function sendMails($message)
    {
        $hostname = gethostname();
        $subject = $this->getMailSubject();
        $headers = 'From: cron-monitor@'.$hostname."\r\n". 'X-Mailer: PHP/'.phpversion();
        foreach ($this->receiver as $rcv) {
            mail(trim($rcv), $subject, $message, $headers);
        }
    }

    /**
     * @return string subject
     */
    private function getMailSubject()
    {
        $hostname = gethostname();

        return sprintf($this->mailSubject, $hostname, date('Y-m-d H:i:s'));
    }
}
