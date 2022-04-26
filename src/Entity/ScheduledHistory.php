<?php

namespace TotalCRM\CommandScheduler\Entity;

use DateTime;
use Exception;

/**
 * Class ScheduledHistory
 * @package TotalCRM\CommandScheduler\Entity
 */
class ScheduledHistory
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int|null
     */
    private $commandId;

    /**
     * @var string|null
     */
    private $messages;

    /**
     * @var string|null
     */
    private $error;

    /**
     * @var DateTime|null
     */
    private $dateStart;

    /**
     * @var DateTime|null
     */
    private $dateFinish;

    /**
     * @var int|null
     */
    private $returnCode;

    /**
     * ScheduledHistory constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->setDateStart(new DateTime());
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
     * @return ScheduledHistory
     */
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCommandId(): ?int
    {
        return $this->commandId;
    }

    /**
     * @param int|null $commandId
     * @return ScheduledHistory
     */
    public function setCommandId(?int $commandId)
    {
        $this->commandId = $commandId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessages(): ?string
    {
        return $this->messages;
    }

    /**
     * @param string|null $messages
     * @return ScheduledHistory
     */
    public function setMessages(?string $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastStart(): ?DateTime
    {
        return $this->dateStart;
    }

    /**
     * @param DateTime|null $dateStart
     * @return ScheduledHistory
     */
    public function setDateStart(?DateTime $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateFinish(): ?DateTime
    {
        return $this->dateFinish;
    }

    /**
     * @param DateTime|null $dateFinish
     * @return ScheduledHistory
     */
    public function setLastFinish(?DateTime $dateFinish)
    {
        $this->dateFinish = $dateFinish;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getReturnCode(): ?int
    {
        return $this->returnCode;
    }

    /**
     * @param int|null $returnCode
     * @return ScheduledHistory
     */
    public function setReturnCode(?int $returnCode)
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return ScheduledHistory
     */
    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }
}