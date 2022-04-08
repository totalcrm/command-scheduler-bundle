<?php

namespace TotalCRM\CommandScheduler\Entity;

use DateTime;
use Exception;

/**
 * Class ScheduledCommand
 * @package TotalCRM\CommandScheduler\Entity
 */
class ScheduledCommand
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $arguments;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $cronExpression;

    /**
     * @var DateTime
     */
    private $lastExecution;

    /**
     * @var int
     */
    private $lastReturnCode;

    /**
     * @var string
     */
    private $logFile;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $executeImmediately;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * @var bool
     */
    private $locked;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var DateTime|null
     */
    private $createdAt;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    /**
     * ScheduledCommand constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->setLastExecution(new DateTime());
        $this->setLocked(false);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return ScheduledCommand
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ScheduledCommand
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return ScheduledCommand
     */
    public function setCommand($command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $arguments
     * @return ScheduledCommand
     */
    public function setArguments($arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->arguments;
    }

    /**
     * @param string $description
     * @return ScheduledCommand
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * @param string $cronExpression
     * @return ScheduledCommand
     */
    public function setCronExpression($cronExpression): self
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastExecution()
    {
        return $this->lastExecution;
    }

    /**
     * @param DateTime $lastExecution
     * @return ScheduledCommand
     */
    public function setLastExecution($lastExecution): self
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     * @return ScheduledCommand
     */
    public function setLogFile($logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * @return int
     */
    public function getLastReturnCode()
    {
        return $this->lastReturnCode;
    }

    /**
     * @param int $lastReturnCode
     * @return ScheduledCommand
     */
    public function setLastReturnCode($lastReturnCode): self
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return ScheduledCommand
     */
    public function setPriority($priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExecuteImmediately()
    {
        return $this->executeImmediately;
    }

    /**
     * @return bool
     */
    public function getExecuteImmediately()
    {
        return $this->executeImmediately;
    }

    /**
     * @param $executeImmediately
     * @return ScheduledCommand
     */
    public function setExecuteImmediately($executeImmediately): self
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return ScheduledCommand
     */
    public function setDisabled($disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return bool
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     * @return ScheduledCommand
     */
    public function setLocked($locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     * @return ScheduledCommand
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param DateTime|null $createdAt
     * @return ScheduledCommand
     */
    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return ScheduledCommand
     */
    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
