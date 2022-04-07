<?php

namespace TotalCRM\CommandScheduler;

use TotalCRM\CommandScheduler\DependencyInjection\CommandSchedulerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CommandSchedulerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return CommandSchedulerExtension
     */
    public function getContainerExtension()
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensionClass()
    {
        return CommandSchedulerExtension::class;
    }
}
