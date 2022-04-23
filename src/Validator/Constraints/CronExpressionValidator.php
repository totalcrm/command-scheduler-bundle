<?php

namespace TotalCRM\CommandScheduler\Validator\Constraints;

use Cron\CronExpression as CronExpressionLib;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CronExpressionValidator
 * @package TotalCRM\CommandScheduler\Validator\Constraints
 */
class CronExpressionValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $value = (string) $value;

        if ('' === $value) {
            return;
        }

        try {
            CronExpressionLib::factory($value);
        } catch (\InvalidArgumentException $e) {
            $this->context->addViolation($constraint->message, [], $value);
        }
    }
}
