<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Faecie\ScheduleBundle\Schedule;

use Doctrine\ORM\EntityManager;
use Faecie\ScheduleBundle\Command\AbstractScheduleCommand;
use Faecie\ScheduleBundle\Command\CommandQueueInterface;
use Faecie\ScheduleBundle\Command\ScheduleCommand;
use Faecie\ScheduleBundle\Entity\JobSchedule;
use Faecie\ScheduleBundle\Entity\JobScheduleExecution;
use Faecie\ScheduleBundle\Exception\InvalidArgumentException;
use Faecie\ScheduleBundle\Exception\RuntimeException;

/**
 * Class ScheduleRunner
 *
 * @internal
 */
class ScheduleRunner
{
    /**
     * Task run timeout in seconds
     */
    const DEFAULT_TIMEOUT = 1800;

    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Entity managers key
     *
     * @var string
     */
    private $emKey;

    /**
     * Collection of entity managers
     *
     * @var EntityManager[] indexed by id
     */
    private $entityManagerCollection;

    /**
     * Operating with tasks
     *
     * @var InnerScheduleService
     */
    private $innerScheduleService;

    /**
     * Test mode feature. List of FQCN of commands that should be forced to execute above the schedule
     *
     * @var string[]
     */
    private $forcedCommands;

    /**
     * Command execution queues collection
     *
     * @var CommandQueueInterface[] indexed by queue identifier
     */
    private $queuesCollection;

    /**
     * Job execution default queue
     *
     * @var CommandQueueInterface
     */
    private $defaultQueue;

    /**
     * Constructor
     *
     * @param InnerScheduleService $innerScheduleService Operating with tasks
     */
    public function __construct(InnerScheduleService $innerScheduleService)
    {
        $this->innerScheduleService = $innerScheduleService;
    }

    /**
     * Set queues
     *
     * @param CommandQueueInterface[] $queuesCollection Queue's collection indexed by identifier
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setQueuesCollection(array $queuesCollection)
    {
        if (!isset($queuesCollection['default'])) {
            throw new InvalidArgumentException('Default queue is not defined. Check the configuration of the bundle');
        }

        $this->defaultQueue     = $queuesCollection['default'];
        $this->queuesCollection = $queuesCollection;
    }

    /**
     * Set forced commands
     *
     * @param array $commands Command names
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setForcedCommands(array $commands)
    {
        foreach ($commands as $command) {
            if ($this->innerScheduleService->validateCommandClass($command)) {
                $this->forcedCommands[] = $command;
            }
        }
    }

    /**
     * Registers entity manager
     *
     * @param string        $key Id of an entity manager
     * @param EntityManager $em  Entity manager
     *
     * @return void
     */
    public function registerEntityManager($key, EntityManager $em)
    {
        $this->entityManagerCollection[$key] = $em;
    }

    /**
     * Run schedule
     *
     * @param string|null $entityManager Entity manager id to run schedule in
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function runSchedule($entityManager = null)
    {
        $scheduleEntityManagers = $entityManager ? [$entityManager] : array_keys($this->entityManagerCollection);

        foreach ($scheduleEntityManagers as $entityManagerKey) {
            $this->setEntityManager($entityManagerKey);

            foreach ($this->getSchedule() as $job) {
                $execution = $this->createExecution($job);
                $this->requireQueue($job)->pushCommand($execution->getCommandClass(), $execution->getArguments());
                $this->innerScheduleService->sendQueued($execution);
            }
        }
    }

    /**
     * Sets the entity manager.
     *
     * @param string $key Id of entity manager
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setEntityManager($key)
    {
        if (!isset($this->entityManagerCollection[$key])) {
            throw new InvalidArgumentException("Wrong entity manager's key {$key} given");
        }

        $this->em    = $this->entityManagerCollection[$key];
        $this->emKey = $key;
        $this->innerScheduleService->setEntityManager($this->em);
    }

    /**
     * Get schedule
     *
     * @return \Traversable Traversable object with JobSchedule elements
     */
    private function getSchedule()
    {
        foreach ($this->innerScheduleService->findScheduledTasks(null, true) as $taskSchedule) {
            $isForcedTask    = in_array(
                trim($taskSchedule->getJob()->getClassName(), '\\'),
                $this->forcedCommands,
                true
            );
            $isTimeToRunTask = floor($taskSchedule->getNextRunDate()->getTimestamp() / 60) === floor(time() / 60);

            if ($isForcedTask || $isTimeToRunTask) {
                yield $taskSchedule;
            }
        }
    }

    /**
     * Creates scheduled job's execution object
     *
     * @param JobSchedule $job Scheduled job
     *
     * @return JobScheduleExecution
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private function createExecution(JobSchedule $job)
    {
        $commandClass   = $job->getJob()->getClassName();
        $taskReflection = new \ReflectionClass($commandClass);

        if ($taskReflection->isSubclassOf(AbstractScheduleCommand::class)) {
            $this->innerScheduleService->validateCommandClass($commandClass);
            $targetClass     = $commandClass;
            $targetArguments = $job->getArguments();

        } else {
            $targetClass     = ScheduleCommand::class;
            $targetArguments = [];
        }

        $execution = new JobScheduleExecution($job, $targetClass);
        $this->innerScheduleService->flushEntity($execution);

        $execution->setArguments(
            array_merge(
                $this->getSystemArguments($execution->getId()),
                $targetArguments
            )
        );
        $this->innerScheduleService->flushEntity($execution);

        return $execution;
    }

    /**
     * Get System arguments
     *
     * @param int $executionId Execution id
     *
     * @return array
     */
    private function getSystemArguments($executionId)
    {
        return [
            'entity-manager' => $this->emKey,
            'execution-id'   => $executionId,
        ];
    }

    /**
     * Get queue to put this job's execution in
     *
     * @param JobSchedule $jobSchedule Schedule of the job
     *
     * @return CommandQueueInterface
     */
    private function requireQueue(JobSchedule $jobSchedule)
    {
        $queueId = $jobSchedule->getQueue();

        if (!$queueId) {
            return $this->defaultQueue;
        }

        return isset($this->queuesCollection[$queueId]) ? $this->queuesCollection[$queueId] : $this->defaultQueue;
    }
}