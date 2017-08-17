<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Faecie\ScheduleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Default status of module
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Constructs a configuration instance
     *
     * @param bool $standalone Core standalone mode
     */
    public function __construct($standalone = false)
    {
        $this->enabled = !$standalone;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('scheduler');

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultValue($this->enabled)->end()
                ->arrayNode('schedule')
                    ->validate()
                        ->ifTrue(function($v) {
                            return !isset($v['entity_managers']) || empty($v['entity_managers']); })
                        ->thenInvalid('"entity_managers" option is not set')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return !isset($v['default_entity_manager']); })
                        ->thenInvalid('"default_entity_manager" is not set')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) {
                            $entityManagers       = $v['entity_managers'];
                            $defaultEntityManager = $v['default_entity_manager'];

                            return !isset($entityManagers[$defaultEntityManager]);
                        })
                        ->thenInvalid('"default_entity_manager" has to be one of "entity_managers"')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) {
                            return !isset($v['queues']) || empty($v['queues']); })
                        ->thenInvalid('"queues" option is not set')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return !isset($v['default_queue']); })
                        ->thenInvalid('"default_queue" is not set')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) {
                            $entityManagers       = $v['entity_managers'];
                            $defaultEntityManager = $v['default_entity_manager'];

                            return !isset($entityManagers[$defaultEntityManager]);
                        })
                        ->thenInvalid('"default_entity_manager" has to be one of "entity_managers"')
                    ->end()
                    ->fixXmlConfig('entity_manager')
                    ->fixXmlConfig('forced_command')
                    ->fixXmlConfig('queue')
                    ->children()
                        ->append($this->getForcedCommandsNode())
                        ->scalarNode('default_entity_manager')->end()
                        ->arrayNode('entity_managers')
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('default_queue')->end()
                        ->arrayNode('queues')
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getForcedCommandsNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('forced_commands');

        $node
            ->prototype('scalar')->end()
                ->defaultValue([])
                ->treatNullLike([])
            ->end();

        return $node;
    }
}
