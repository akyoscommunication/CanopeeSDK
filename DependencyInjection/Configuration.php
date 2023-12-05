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
                    ->arrayNode('api')
                    ->children()
                        ->scalarNode('client_id')->defaultValue('%env(CANOPEESDK_CLIENT_ID)%')->end()
                        ->scalarNode('client_secret')->defaultValue('%env(CANOPEESDK_CLIENT_SECRET)%')->end()
                        ->scalarNode('endpoint')->defaultValue('%env(CANOPEESDK_ENPOINT)%')->end()
                    ->end()
                ->end()
                ->arrayNode('entity')
                    ->children()
                    ->scalarNode('user_entity')->defaultValue('%env(CANOPEE_SDK_USER_ENTITY)%')->end()
                    ->scalarNode('customer_entity')->defaultValue('%env(CANOPEE_SDK_CUSTOMER_ENTITY)%')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}