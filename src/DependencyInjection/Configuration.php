<?php

namespace TotalCRM\CommandScheduler\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package TotalCRM\CommandScheduler\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('totalcrm_command_scheduler');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('totalcrm_command_scheduler');
        }

        $rootNode
            ->children()
                ->scalarNode('doctrine_manager')->defaultValue('default')->end()
                ->scalarNode('log_path')->defaultValue('%kernel.logs_dir%')->end()
                ->scalarNode('lock_timeout')->defaultValue(false)->end()
                ->arrayNode('monitor_mail')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('monitor_mail_subject')->defaultValue('cronjob monitoring %%s, %%s')->end()
                ->booleanNode('send_ok')->defaultValue(false)->end()
                ->variableNode('excluded_command_namespaces')
                    ->defaultValue([])
                    ->validate()
                        ->always(function ($value) {
                            if (is_string($value)) {
                                return explode(',', $value);
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->variableNode('included_command_namespaces')
                    ->defaultValue([])
                    ->validate()
                        ->always(function ($value) {
                            if (is_string($value)) {
                                return explode(',', $value);
                            }

                            return $value;
                        })
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
