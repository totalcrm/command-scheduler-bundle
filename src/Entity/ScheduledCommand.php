<?php

namespace TotalCRM\CommandScheduler\Entity;

use DateTime;

/**
 * Entity ScheduledCommand.
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
    private $error;

    /**
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     *
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
     * Log's file name (without path).
     *
     * @var string
     */
    private $logFile;

    /**
     * @var int
     */
    private $priority;

    /**
     * If true, command will be execute next time regardless cron expression.
     *
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
     * Init new ScheduledCommand.
     */
    public function __construct()
    {
        $this->setLastExecution(new DateTime());
        $this->setLocked(false);
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set command.
     *
     * @param string $command
     *
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return string
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param string $arguments
     *
     * @return $this
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->arguments;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get cronExpression.
     *
     * @return string
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * Set cronExpression.
     *
     * @param string $cronExpression
     *
     * @return $this
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     * Get lastExecution.
     *
     * @return DateTime
     */
    public function getLastExecution()
    {
        return $this->lastExecution;
    }

    /**
     * Set lastExecution.
     *
     * @param DateTime $lastExecution
     *
     * @return $this
     */
    public function setLastExecution($lastExecution)
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    /**
     * Get logFile.
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * Set logFile.
     *
     * @param string $logFile
     *
     * @return $this
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Get lastReturnCode.
     *
     * @return int
     */
    public function getLastReturnCode()
    {
        return $this->lastReturnCode;
    }

    /**
     * Set lastReturnCode.
     *
     * @param int $lastReturnCode
     *
     * @return $this
     */
    public function setLastReturnCode($lastReturnCode)
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get executeImmediately.
     *
     * @return bool
     */
    public function isExecuteImmediately()
    {
        return $this->executeImmediately;
    }

    /**
     * Get executeImmediately.
     *
     * @return bool
     */
    public function getExecuteImmediately()
    {
        return $this->executeImmediately;
    }

    /**
     * Set executeImmediately.
     *
     * @param $executeImmediately
     *
     * @return $this
     */
    public function setExecuteImmediately($executeImmediately)
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    /**
     * Get disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Get disabled.
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Locked Getter.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * locked Getter.
     *
     * @return bool
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * locked Setter.
     *
     * @param bool $locked
     *
     * @return $this
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

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
