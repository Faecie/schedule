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
use Faecie\ScheduleBundle\Console\Command\CommandExecutor;
use Faecie\ScheduleBundle\Entity\Job;
use Faecie\ScheduleBundle\Entity\JobSchedule;
use Faecie\ScheduleBundle\Entity\JobScheduleExecution;
use Faecie\ScheduleBundle\Enum\ExecutionStateEnum;
use Faecie\ScheduleBundle\Enum\ExecutionStateMessageEnum;
use Faecie\ScheduleBundle\Exception\InvalidArgumentException;
use Faecie\ScheduleBundle\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class InnerScheduleService
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 * @internal
 */
class InnerScheduleService
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Kernel
     *
     * @var KernelInterface
     */
    private $kernel;

    /**
     * InnerScheduleService constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * setEntityManager
     *
     * @param EntityManager $entityManager Entity manager
     *
     * @return void
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Find last run results
     *
     * @param JobSchedule  $task         The task to search
     * @param boolean|null $isSuccessful The flag whether to search for successful iteration
     * @param int          $number       Last iteration count
     *
     * @return JobScheduleExecution[]
     */
    public function findLastIterationsCollection(JobSchedule $task, $isSuccessful = null, $number = 1)
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('tsrr')
            ->from(JobScheduleExecution::class, 'tsrr')
            ->where('tsrr.taskSchedule = :taskSchedule')
            ->setParameter('taskSchedule', $task);

        if ($isSuccessful !== null) {
            $qb->andWhere('tsrr.isSuccessful = :isSuccessful')
                ->setParameter('isSuccessful', (bool) $isSuccessful);
        }

        return $qb
            ->orderBy('tsrr.iterationNumber', 'DESC')
            ->setMaxResults($number)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find tasks in schedule
     *
     * @param Job|null     $task      Task object
     * @param boolean|null $isEnabled Flag whether to find only enabled tasks
     *
     * @return JobSchedule[]
     */
    public function findScheduledTasks(Job $task = null, $isEnabled = null)
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('ts')
            ->from(JobSchedule::class, 'ts')
            ->innerJoin('ts.task', 't', 'WITH', 'ts.task = t.id');

        if ($task) {
            $qb->andWhere('t.id = :taskId')
                ->setParameter('taskId', $task->getId());
        }

        if ($isEnabled !== null) {
            if (!$isEnabled) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('t.isEnabled', false),
                        $qb->expr()->eq('ts.isEnabled', false)
                    )
                );
            } else {
                $qb->andWhere('t.isEnabled = true')
                    ->andWhere('ts.isEnabled = true');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get task name
     *
     * @param string $taskClassName Full task class name
     *
     * @return string
     */
    public function getCommandName($taskClassName)
    {
        /** @var Command $command */
        $command = new $taskClassName();

        return $command->getName();
    }

    /**
     * Validates command
     *
     * @param string $command Console command
     *
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function validateCommandClass($command)
    {
        if (!class_exists($command)) {
            throw new InvalidArgumentException(sprintf("Command '%s' is not defined", $command));
        }

        $commandInterfaces = class_implements($command);
        if (!isset($commandInterfaces[Command::class])) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid command '%s' provided. Must implement %s interface",
                    $command,
                    Command::class
                )
            );
        }

        try {
            new $command();
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return true;
    }

    /**
     * Sends successful flag for execution object
     *
     * @param JobScheduleExecution $execution Job schedule execution
     *
     * @return void
     * @throws RuntimeException Rolls back transaction if failure
     */
    public function sendSuccess(JobScheduleExecution $execution)
    {
        $execution->setState(ExecutionStateEnum::SUCCESS);
        $execution->setFinishDate(new \DateTime());
        $execution->setLastResultInfo(ExecutionStateMessageEnum::SUCCESS);
        $this->flushEntity($execution);
    }

    /**
     * Wraps common doctrine flush
     *
     * @param object $entity Doctrine entity
     *
     * @throws RuntimeException
     */
    public function flushEntity($entity)
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Sends started flag for execution object
     *
     * @param JobScheduleExecution $execution Job schedule execution
     *
     * @return void
     * @throws RuntimeException Rolls back transaction if failure
     */
    public function sendStarted(JobScheduleExecution $execution)
    {
        $execution->setState(ExecutionStateEnum::IS_RUNNING);
        $execution->setLastResultInfo(ExecutionStateMessageEnum::IS_RUNNING);
        $execution->setFinishDate(null);

        $this->flushEntity($execution);
    }

    /**
     * Sends queued flag for execution object
     *
     * @param JobScheduleExecution $execution Job schedule execution
     *
     * @return void
     * @throws RuntimeException Rolls back transaction if failure
     */
    public function sendQueued(JobScheduleExecution $execution)
    {
        $execution->setState(ExecutionStateEnum::QUEUED);
        $execution->setLastResultInfo(ExecutionStateMessageEnum::QUEUED);
        $execution->setFinishDate(null);

        $this->flushEntity($execution);
    }

    /**
     * Sends fail flag for execution object
     *
     * @param JobScheduleExecution $execution Job schedule execution
     * @param \Exception           $e         Exception which caused fail
     *
     * @return void
     * @throws RuntimeException Rolls back transaction if failure
     */
    public function sendFail(JobScheduleExecution $execution, \Exception $e)
    {
        $execution->setState(ExecutionStateEnum::FAILURE);
        $execution->setFinishDate(null);
        $execution->setLastResultInfo($e->getMessage());
        $this->flushEntity($execution);
    }
}