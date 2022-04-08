<?php

namespace TotalCRM\CommandScheduler\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class CommandSchedulerExtension
 * @package TotalCRM\CommandScheduler\DependencyInjection
 */
class CommandSchedulerExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config as $key => $value) {
            $container->setParameter('totalcrm_command_scheduler.'.$key, $value);
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'totalcrm_command_scheduler';
    }
}
