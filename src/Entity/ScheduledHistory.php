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
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $commandId;

    /**
     * @var string
     */
    private $messages;

    /**
     * @var string
     */
    private $error;

    /**
     * @var DateTime
     */
    private $dateExecution;

    /**
     * @var int
     */
    private $returnCode;

    /**
     * ScheduledHistory constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->setDateExecution(new DateTime());
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
     * @return ScheduledHistory
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCommandId()
    {
        return $this->commandId;
    }

    /**
     * @param int $commandId
     * @return ScheduledHistory
     */
    public function setCommandId($commandId)
    {
        $this->commandId = $commandId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $messages
     * @return ScheduledHistory
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateExecution()
    {
        return $this->dateExecution;
    }

    /**
     * @param DateTime $dateExecution
     * @return ScheduledHistory
     */
    public function setDateExecution($dateExecution)
    {
        $this->dateExecution = $dateExecution;

        return $this;
    }

    /**
     * @return int
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @param int $returnCode
     * @return ScheduledHistory
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return ScheduledHistory
     */
    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }
}