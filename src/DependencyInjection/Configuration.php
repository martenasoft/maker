<?php

namespace MartenaSoft\Maker\DependencyInjection;

use MartenaSoft\Maker\MartenaSoftMakerBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public static function getDirectories(): array
    {
        return [
            'Controller',
            'Entity',
            'Repository',
            'Form',
            'Command',
            'Resources',
            'Service'
        ];
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(MartenaSoftMakerBundle::getConfigName());

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode("root")
                    ->defaultValue(realpath(__DIR__ . '/../../../../'))
                ->end()
            ->end()
            ->children()
                ->scalarNode('directories')
                    ->defaultValue(self::getDirectories())
                    ->end()
                ->end()
            ->children()
                ->arrayNode('bundles')
                    ->addDefaultChildrenIfNoneSet()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('dir')->defaultValue("martenasoft")->end()
                            ->scalarNode('reader')->defaultValue("default")->end()
                      //      ->scalarNode('password')->defaultValue("sdsd")->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
