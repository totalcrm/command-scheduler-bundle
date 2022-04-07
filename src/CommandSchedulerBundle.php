<?php

namespace TotalCRM\CommandScheduler;

use TotalCRM\CommandScheduler\DependencyInjection\TotalCRMCommandSchedulerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CommandSchedulerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return TotalCRMCommandSchedulerExtension
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
        return TotalCRMCommandSchedulerExtension::class;
    }
}
