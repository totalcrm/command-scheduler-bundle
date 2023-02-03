<?php

namespace TotalCRM\CommandScheduler\Entity\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\TransactionRequiredException;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;

/**
 * Class ScheduledCommandRepository
 * @package TotalCRM\CommandScheduler\Entity\Repository
 */
class ScheduledCommandRepository extends EntityRepository
{
    /**
     * @return object[]|ScheduledCommand[]|null
     */
    public function findEnabledCommand(): ?array
    {
        $query = $this
            ->createQueryBuilder('command')
            ->andWhere('command.disabled = false AND (command.locked = false OR command.autoLocked = true)')
            ->orWhere('command.executeImmediately = 1')
            ->orderBy('command.priority', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $query;
    }

    /**
     * @return object[]|ScheduledCommand[]|null
     */
    public function findAll(): ?array
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }

    /**
     * @return object[]|ScheduledCommand[]|null
     */
    public function findLockedCommand(): ?array
    {
        $query = $this
            ->createQueryBuilder('command')
            ->andWhere('command.disabled = false')
            ->andWhere('command.locked = true')
            ->orderBy('command.priority', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $query;
    }

    /**
     * @return object[]|ScheduledCommand[]|null
     */
    public function findFailedCommand(): ?array
    {
        $query = $this
            ->createQueryBuilder('command')
            ->where('command.disabled = false')
            ->andWhere('command.lastReturnCode != 0')
            ->getQuery()
            ->getResult()
        ;
        
        return $query;
    }

    /**
     * @param int|bool $lockTimeout
     * @return object[]|ScheduledCommand[]|null
     */
    public function findFailedAndTimeoutCommands($lockTimeout = false): ?array
    {
        $failedCommands = $this->findFailedCommand();
        if (false !== $lockTimeout) {
            $lockedCommands = $this->findLockedCommand();
            foreach ($lockedCommands as $lockedCommand) {
                $now = time();
                if ($lockedCommand->getLastStart()->getTimestamp() + $lockTimeout < $now) {
                    $failedCommands[] = $lockedCommand;
                }
            }
        }

        return $failedCommands;
    }

    /**
     * @param int $commandId
     * @return ScheduledCommand|null
     * @throws NonUniqueResultException|TransactionRequiredException
     */
    public function getNotLockedCommand(int $commandId): ?ScheduledCommand
    {
        $query = $this
            ->createQueryBuilder('command')
            ->andWhere('command.locked = false OR command.autoLocked = true')
            ->andWhere('command.id = :id')
            ->setParameter('id', $commandId)
            ->getQuery()
        ;

        $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

        return $query->getOneOrNullResult();
    }
}
