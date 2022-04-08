<?php

namespace TotalCRM\CommandScheduler;

use TotalCRM\CommandScheduler\DependencyInjection\CommandSchedulerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CommandSchedulerBundle
 * @package TotalCRM\CommandScheduler
 */
class CommandSchedulerBundle extends Bundle
{
    /**
     * @return CommandSchedulerExtension
     */
    public function getContainerExtension()
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * @return string
     */
    protected function getContainerExtensionClass()
    {
        return CommandSchedulerExtension::class;
    }
}
