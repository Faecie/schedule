<?php


namespace Faecie\ScheduleBundle\Schedule;

use DateTime;
use Doctrine\ORM\EntityManager;
use Faecie\ScheduleBundle\Entity\AbstractJobSchedule;
use Faecie\ScheduleBundle\Entity\Job;
use Faecie\ScheduleBundle\Entity\JobSchedule;
use Faecie\ScheduleBundle\Entity\JobScheduleExecution;
use Faecie\ScheduleBundle\Exception\InvalidArgumentException;
use Faecie\ScheduleBundle\Exception\RuntimeException;

/**
 * Service to work with schedule
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 */
class ScheduleService
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Inner service that operates with tasks
     *
     * @var InnerScheduleService
     */
    private $innerScheduleService;

    /**
     * Constructor
     *
     * @param EntityManager        $entityManager        Entity manager
     * @param InnerScheduleService $innerScheduleService Inner service that operates with tasks
     */
    public function __construct(EntityManager $entityManager, InnerScheduleService $innerScheduleService)
    {
        $this->entityManager        = $entityManager;
        $this->innerScheduleService = $innerScheduleService;
        $this->innerScheduleService->setEntityManager($this->entityManager);
    }

    /**
     * Get last execution of the job
     *
     * @param JobSchedule  $job          The job in schedule to search
     * @param boolean|null $isSuccessful The flag whether to search for successful execution
     *
     * @return JobScheduleExecution|null
     */
    public function findLastIteration(JobSchedule $job, $isSuccessful = null)
    {
        $iterations = $this->findLastIterationsCollection($job, $isSuccessful, 1);

        return $iterations ? reset($iterations) : null;
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
        return $this->innerScheduleService->findLastIterationsCollection($task, $isSuccessful, $number);
    }

    /**
     * Find tasks in schedule
     *
     * @param Job          $task      Task object
     * @param boolean|null $isEnabled Flag whether to find only enabled tasks
     *
     * @return JobSchedule[]
     */
    public function findScheduledTasks(Job $task, $isEnabled = null)
    {
        return $this->innerScheduleService->findScheduledTasks($task, $isEnabled);
    }

    /**
     * Schedules a task for processing
     *
     * @param string    $commandClass Identifier of task class to call
     * @param array     $arguments    Additional arguments for invocation
     * @param \DateTime $time         The time into the day to run the task
     * @param int       $frequency    The time in minutes to wait before scheduling the message again
     *
     * @return JobSchedule
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function scheduleTask(
        $commandClass,
        array $arguments = [],
        \DateTime $time = null,
        $frequency = 60
    ) {
        $time = $time ?: new DateTime('+5 minutes');
        $this->innerScheduleService->validateCommandClass($commandClass);

        $job         = $this->requireJobByName($commandClass);
        $jobSchedule = new JobSchedule($frequency, $time, $job);
        $jobSchedule->setArguments($arguments);
        $this->innerScheduleService->flushEntity($jobSchedule);

        return $jobSchedule;
    }

    /**
     * Acquires a task according to task class name
     *
     * @param string $commandClassName command's FQCN
     *
     * @return Job
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function requireJobByName($commandClassName)
    {
        $job = $this->entityManager->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->where('j.command = :commandName')
            ->setParameter('commandName', $commandClassName)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$job) {
            $this->innerScheduleService->validateCommandClass($commandClassName);

            try {
                $job = new Job();
                $job->setClassName($commandClassName);
                $job->setSystemName($this->innerScheduleService->getCommandName($commandClassName));
                $this->entityManager->persist($job);
                $this->entityManager->flush($job);
            } catch (\Throwable $e) {
                throw new RuntimeException($e->getMessage());
            }
        }

        return $job;
    }

    /**
     * Save task schedule in the data base
     *
     * @param AbstractJobSchedule $schedule Scheduled task
     *
     * @return void
     * @throws RuntimeException
     */
    public function save(AbstractJobSchedule $schedule)
    {
        $this->innerScheduleService->flushEntity($schedule);
    }

    /**
     * Require job by id
     *
     * @param integer $jobId Job id
     *
     * @return JobSchedule
     * @throws RuntimeException
     */
    public function requireJobById($jobId)
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->select('js')
                ->from(JobSchedule::class, 'js')
                ->where('js.id = :jobId')
                ->setParameter('jobId', $jobId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Require concrete execution by id
     *
     * @param integer $execId Execution id
     *
     * @return JobScheduleExecution
     * @throws RuntimeException
     */
    public function requireJobScheduleExecutionById($execId)
    {
        try {
            return $this->entityManager->createQueryBuilder()
                ->select('jse')
                ->from(JobScheduleExecution::class, 'jse')
                ->where('jse.id = :execId')
                ->setParameter('execId', $execId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}