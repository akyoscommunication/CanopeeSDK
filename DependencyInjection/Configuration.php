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
            ->arrayNode('entity')
            ->children()
            ->scalarNode('user_entity')->defaultValue('%env(CANOPEE_SDK_USER_ENTITY)%')->end()
            ->scalarNode('user_access_right_entity')->defaultValue('%env(CANOPEE_SDK_USER_ACCESS_RIGHT_ENTITY)%')->end()
            ->scalarNode('customer_entity')->defaultValue('%env(CANOPEE_SDK_CUSTOMER_ENTITY)%')->end()
            ->scalarNode('customer_access_right_entity')->defaultValue('%env(CANOPEE_SDK_CUSTOMER_ACCESSS_RIGHT_ENTITY)%')->end()
            ->end()
            ->end()
            ->scalarNode('module_slug')->defaultValue('%env(CANOPEE_SDK_MODULE_SLUG)%')->end()
            ->scalarNode('host')->defaultValue('%env(HOST)%')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
