<?php

namespace TotalCRM\CommandScheduler\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CronExpression
 * @package TotalCRM\CommandScheduler\Validator\Constraints
 */
class CronExpression extends Constraint
{
    /**
     * Constraint error message.
     *
     * @var string
     */
    public $message;
}
