<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Faecie\ScheduleBundle\DependencyInjection\Compiler;

use Faecie\ScheduleBundle\Schedule\ScheduleAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Injects schedule service in tagged definitions
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 */
class ScheduleInjectorPass implements CompilerPassInterface
{
    /**
     * Encryptor aware tag name
     */
    const SCHEDULE_AWARE_TAG = 'faecie.schedule.schedule.aware';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('faecie.schedule.schedule_enabled')) {
            return;
        }

        foreach ($container->findTaggedServiceIds(self::SCHEDULE_AWARE_TAG) as $id => $tags) {
            $targetDefinition           = $container->getDefinition($id);
            $targetDefinitionClass      = $container->getParameterBag()->resolveValue($targetDefinition->getClass());
            $targetDefinitionInterfaces = class_implements($targetDefinitionClass);
            if (!isset($targetDefinitionInterfaces[ScheduleAwareInterface::class])) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Cannot inject schedule service in '%s'. " .
                        "Consumer definition class '%s' must implement '%s' interface",
                        $id,
                        $targetDefinitionClass,
                        ScheduleAwareInterface::class
                    )
                );
            }

            $defaultSchedule          = new Reference('faecie.schedule.service.default.schedule');
            $defaultEntityManagerName = $container->getParameter("faecie.schedule.config.default_entity_manager");
            foreach ($tags as $tag) {
                if (isset($tag['entity_manager'])) {
                    $entityManagerKey = $tag['entity_manager'];
                    $schedule         = new Reference('faecie.schedule.service.task.schedule.' . $tag['entity_manager']);
                } else {
                    $entityManagerKey = $defaultEntityManagerName;
                    $schedule         = $defaultSchedule;
                }

                $targetDefinition->addMethodCall('setSchedule', [$schedule, $entityManagerKey]);
            }
        }
    }
}
