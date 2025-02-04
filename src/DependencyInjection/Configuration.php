<?php

namespace Omidrezasalari\CircuitBreakerBundle\DependencyInjection;

use Omidrezasalari\CircuitBreakerBundle\Service\ApcuStorage;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('circuit_breaker');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('storage_service')
            ->defaultValue(ApcuStorage::class)
            ->end()
            ->integerNode('failure_threshold')->defaultValue(5)->end()
            ->integerNode('timeout_period')->defaultValue(60)->end()
            ->end();

        return $treeBuilder;
    }
}
