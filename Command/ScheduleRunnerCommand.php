<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Faecie\ScheduleBundle\Command;

use Faecie\ScheduleBundle\Schedule\ScheduleRunner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Schedule task
 */
class ScheduleRunnerCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('faecie:schedule:runner')
            ->addArgument('entity-manager', InputArgument::OPTIONAL, 'Entity manager id');
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $input->getArgument('entity-manager');

        /** @var ScheduleRunner $taskScheduleService */
        $schedule = $this->getContainer()->get('faecie.schedule.service.schedule.runner');
        $schedule->runSchedule($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Command that runs tasks by schedule';
    }

}