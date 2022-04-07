<?php

namespace TotalCRM\CommandSchedulerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CronExpression.
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
