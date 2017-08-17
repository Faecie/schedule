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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Asynchronous task bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ScheduleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $parameters    = $this->processConfiguration($configuration, $configs);

        // Do not load a configuration for bundle if it isn't enabled
        if (empty($parameters['enabled'])) {
            return;
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (($parameters['schedule_enabled'])) {
            $container->setParameter('faecie.schedule.schedule_enabled', $parameters['schedule_enabled']);
            $this->defineScheduleServices($container, $parameters['schedule']);
        }
    }

    /**
     * Define schedule services for entity managers
     *
     * @param ContainerBuilder $container Container
     * @param array            $config    Configuration
     *
     * @return void
     */
    private function defineScheduleServices(ContainerBuilder $container, array $config)
    {
        if ($container->getParameter('kernel.debug') && $config['forced_tasks']) {
            $scheduleRunner = $container->getDefinition('faecie.schedule.service.schedule.runner');
            $scheduleRunner->addMethodCall('setForcedCommands', [$config['forced_commands']]);
        }

        $container->setParameter("faecie.schedule.config.default_entity_manager", $config['default_entity_manager']);
        $innerTaskScheduleService = new Reference('faecie.schedule.service.schedule.inner');

        foreach ($config['entity_managers'] as $key => $em) {
            $serviceId = 'faecie.schedule.service.task.schedule.' . $key;
            $decorator = new DefinitionDecorator('faecie.schedule.service.schedule');
            $decorator->setArguments([new Reference($em), $innerTaskScheduleService]);
            $container->setDefinition($serviceId, $decorator);
            if ($config['default_entity_manager'] === $key) {
                $container->setAlias('faecie.schedule.service.default.schedule', $serviceId);
            }
        }

        $schedule = $container->getDefinition("faecie.schedule.service.schedule.runner");
        foreach ($config['entity_managers'] as $key => $em) {
            $schedule->addMethodCall(
                'registerEntityManager',
                array(
                    $key,
                    new Reference($em)
                )
            );
        }
    }
}
