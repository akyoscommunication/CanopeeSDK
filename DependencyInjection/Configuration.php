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
                    ->scalarNode('client_id')->defaultValue('%env(CANOPEESDK_CLIENT_ID)%')->end()
                    ->scalarNode('client_secret')->defaultValue('%env(CANOPEESDK_CLIENT_SECRET)%')->end()
                    ->scalarNode('endpoint')->defaultValue('%env(CANOPEESDK_ENPOINT)%')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}