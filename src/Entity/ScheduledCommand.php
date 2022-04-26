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
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $command;

    /**
     * @var string|null
     */
    private $arguments;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $cronExpression;

    /**
     * @var DateTime|null
     */
    private $lastStart;

    /**
     * @var DateTime|null
     */
    private $lastFinish;

    /**
     * @var int|null
     */
    private $lastReturnCode;

    /**
     * @var string|null
     */
    private $lastMessages;

    /**
     * @var string|null
     */
    private $logFile;

    /**
     * @var bool|null
     */
    private $isHistory;

    /**
     * @var int|null
     */
    private $priority;

    /**
     * @var bool|null
     */
    private $executeImmediately;

    /**
     * @var bool|null
     */
    private $disabled;

    /**
     * @var bool|null
     */
    private $locked;

    /**
     * @var bool|null
     */
    private $autoLocked;

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
        $this->setLocked(false);
        $this->setAutoLocked(false);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null
     * @return ScheduledCommand
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ScheduledCommand
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * @param string|null $command
     * @return ScheduledCommand
     */
    public function setCommand(?string $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    /**
     * @param string|null $arguments
     * @return ScheduledCommand
     */
    public function setArguments(?string $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return ScheduledCommand
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    /**
     * @param string|null $cronExpression
     * @return ScheduledCommand
     */
    public function setCronExpression(?string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }
    
    /**
     * @return DateTime|null
     */
    public function getLastStart(): ?DateTime
    {
        return $this->lastStart;
    }

    /**
     * @param DateTime|null $lastStart
     * @return ScheduledCommand
     */
    public function setLastStart($lastStart): self
    {
        $this->lastStart = $lastStart;

        return $this;
    }
    
    /**
     * @return DateTime|null
     */
    public function getLastFinish(): ?DateTime
    {
        return $this->lastFinish;
    }

    /**
     * @param DateTime|null $lastFinish
     * @return ScheduledCommand
     */
    public function setLastFinish($lastFinish): self
    {
        $this->lastFinish = $lastFinish;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * @param string|null $logFile
     * @return ScheduledCommand
     */
    public function setLogFile(?string $logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    /**
     * @param int|null $lastReturnCode
     * @return ScheduledCommand
     */
    public function setLastReturnCode(?int $lastReturnCode): self
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int|null $priority
     * @return ScheduledCommand
     */
    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isExecuteImmediately(): ?bool
    {
        return $this->executeImmediately;
    }

    /**
     * @return bool|null
     */
    public function getExecuteImmediately(): ?bool
    {
        return $this->executeImmediately;
    }

    /**
     * @param bool|null $executeImmediately
     * @return ScheduledCommand
     */
    public function setExecuteImmediately($executeImmediately): self
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * @return bool|null
     */
    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * @param bool|null $disabled
     * @return ScheduledCommand
     */
    public function setDisabled(?bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * @return bool|null
     */
    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * @param bool|null $locked
     * @return ScheduledCommand
     */
    public function setLocked(?bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
    
    /**
     * @return bool|null
     */
    public function isAutoLocked(): ?bool
    {
        return $this->autoLocked;
    }

    /**
     * @return bool|null
     */
    public function getAutoLocked(): ?bool
    {
        return $this->autoLocked;
    }

    /**
     * @param bool|null $autoLocked
     * @return ScheduledCommand
     */
    public function setAutoLocked(?bool $autoLocked): self
    {
        $this->autoLocked = $autoLocked;

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

    /**
     * @return bool|null
     */
    public function isHistory(): ?bool
    {
        return $this->isHistory;
    }

    /**
     * @param bool|null $isHistory
     * @return ScheduledCommand
     */
    public function setIsHistory(?bool $isHistory): self
    {
        $this->isHistory = $isHistory;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastMessages(): ?string
    {
        return $this->lastMessages;
    }

    /**
     * @param string|null $lastMessages
     * @return ScheduledCommand
     */
    public function setLastMessages(?string $lastMessages): self
    {
        $this->lastMessages = $lastMessages;

        return $this;
    }
}
