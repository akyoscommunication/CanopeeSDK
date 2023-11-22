<?php

namespace Akyos\CanopeeSDK\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('canopee_sdk');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->arrayNode('token')
                        ->children()
                            ->scalarNode('key')->defaultValue('%env(CANOPEESDK_KEY)%')->end()
                            ->scalarNode('algo')->defaultValue('HS256')->end()
                            ->scalarNode('validity_time')->defaultValue('60')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}