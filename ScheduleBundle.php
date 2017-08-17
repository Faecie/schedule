<?php
namespace Faecie\ScheduleBundle;

use Faecie\ScheduleBundle\DependencyInjection\Compiler\ScheduleInjectorPass;
use Faecie\ScheduleBundle\Service\Queue;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for scheduling of task.
 */
class ScheduleBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Queue::setContainer($this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ScheduleInjectorPass());
    }
}
