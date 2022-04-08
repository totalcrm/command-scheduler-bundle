<?php

namespace TotalCRM\CommandScheduler\Entity;

use DateTime;

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
     * Init new ScheduledHistory.
     */
    public function __construct()
    {
        $this->setDateExecution(new DateTime());
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get commandId.
     *
     * @return int
     */
    public function getCommandId()
    {
        return $this->commandId;
    }

    /**
     * Set commandId.
     *
     * @param int $commandId
     *
     * @return $this
     */
    public function setCommandId($commandId)
    {
        $this->commandId = $commandId;

        return $this;
    }

    /**
     * Get messages.
     *
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set messages.
     *
     * @param string $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get dateExecution.
     *
     * @return DateTime
     */
    public function getDateExecution()
    {
        return $this->dateExecution;
    }

    /**
     * Set dateExecution.
     *
     * @param DateTime $dateExecution
     *
     * @return $this
     */
    public function setDateExecution($dateExecution)
    {
        $this->dateExecution = $dateExecution;

        return $this;
    }

    /**
     * Get returnCode.
     *
     * @return int
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * Set lastReturnCode.
     *
     * @param int $returnCode
     *
     * @return $this
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
     * @return $this
     */
    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }
}