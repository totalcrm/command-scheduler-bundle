<?php

namespace TotalCRM\CommandScheduler\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CronExpression
 * @package TotalCRM\CommandScheduler\Validator\Constraints
 */
class CronExpression extends Constraint
{
    public string $message;
}
