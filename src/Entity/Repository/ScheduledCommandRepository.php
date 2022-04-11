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
     * @return object[]|ScheduledCommand[]
     */
    public function findEnabledCommand()
    {
        return $this->findBy(['disabled' => false, 'locked' => false], ['priority' => 'DESC']);
    }

    /**
     * @return object[]|ScheduledCommand[]
     */
    public function findAll()
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }

    /**
     * @return object[]|ScheduledCommand[]
     */
    public function findLockedCommand()
    {
        return $this->findBy(['disabled' => false, 'locked' => true], ['priority' => 'DESC']);
    }

    /**
     * @return object[]|ScheduledCommand[]
     */
    public function findFailedCommand()
    {
        return $this->createQueryBuilder('command')
            ->where('command.disabled = false')
            ->andWhere('command.lastReturnCode != 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int|bool $lockTimeout
     *
     * @return object[]|ScheduledCommand[]
     */
    public function findFailedAndTimeoutCommands($lockTimeout = false)
    {
        // Fist, get all failed commands (return != 0)
        $failedCommands = $this->findFailedCommand();

        // Then, si a timeout value is set, get locked commands and check timeout
        if (false !== $lockTimeout) {
            $lockedCommands = $this->findLockedCommand();
            foreach ($lockedCommands as $lockedCommand) {
                $now = time();
                if ($lockedCommand->getLastExecution()->getTimestamp() + $lockTimeout < $now) {
                    $failedCommands[] = $lockedCommand;
                }
            }
        }

        return $failedCommands;
    }

    /**
     * @param int $commandId
     *
     * @return ScheduledCommand|null
     *
     * @throws NonUniqueResultException|TransactionRequiredException
     */
    public function getNotLockedCommand(int $commandId)
    {
        $query = $this->createQueryBuilder('command')
            ->where('command.locked = false')
            ->andWhere('command.id = :id')
            ->setParameter('id', $commandId)
            ->getQuery();

        $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

        return $query->getOneOrNullResult();
    }
}
