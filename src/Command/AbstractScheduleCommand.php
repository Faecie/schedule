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

use Exception;
use Faecie\ScheduleBundle\Entity\JobScheduleExecution;
use Faecie\ScheduleBundle\Schedule\ScheduleService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract schedule task
 */
abstract class AbstractScheduleCommand extends ContainerAwareCommand
{
    /**
     * Service working with schedule
     *
     * @var ScheduleService
     */
    protected $scheduleService;

    /**
     * History record for operating task
     *
     * @var JobScheduleExecution
     */
    protected $execution;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this->addArgument('entity-manager', InputArgument::REQUIRED, 'Entity manager id')
            ->addArgument('execution-id', InputArgument::REQUIRED, 'Task iteration id');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runResultId           = (int) $input->getOption('execution-id');
        $scheduleServiceId     = 'faecie.schedule.service.task.schedule.' . $input->getOption('entity-manager');
        $this->scheduleService = $this->getContainer()->get($scheduleServiceId);
        $this->execution       = $this->scheduleService->requireJobScheduleExecutionById($runResultId);
        $innerService          = $this->getContainer()->get('faecie.schedule.service.schedule.inner');

        $output->writeln("Starting to run iteration {$runResultId}");

        try {
            $innerService->sendStarted($this->execution);
            $this->executeCommand($input, $output);
            $innerService->sendSuccess($this->execution);
        } catch (Exception $e) {
            $innerService->sendFail($this->execution, $e);
            throw $e;
        }
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return void
     */
    protected abstract function executeCommand(InputInterface $input, OutputInterface $output);
}