<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Faecie\ScheduleBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class JobSchedule
 *
 * @author Ruslan Umyarov <umyarovrr@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="job_schedule")
 * @ORM\HasLifecycleCallbacks()
 */
class JobSchedule extends AbstractJobSchedule
{
    /**
     * Job object
     *
     * @var Job
     * @ORM\ManyToOne(targetEntity="Job",cascade={"persist"})
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private $job;

    /**
     * Initial time when this job has to be run at first
     *
     * @var \DateTime
     * @ORM\Column(name="time", type="time")
     */
    private $time;

    /**
     * Is enabled flag
     *
     * @var boolean
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

    /**
     * The time when this schedule was created
     *
     * @var \DateTime
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * Last updated date for this schedule
     *
     * @var \DateTime
     * @ORM\Column(name="updated_date", type="datetime")
     */
    private $updatedDate;

    /**
     * Identifier of preferred queue to push execution in
     *
     * @var string
     * @ORM\Column(name="queue", type="string", nullable="true")
     */
    private $queue;

    /**
     * Executions of this job
     *
     * @var ArrayCollection
     * @ORM\OneToMany(
     *   targetEntity="JobScheduleExecution",
     *   mappedBy="jobSchedule",
     *   indexBy="id"
     *  )
     */
    private $taskScheduledRunResult;

    /**
     * Constructor
     *
     * @param int      $frequency Frequency between executing
     * @param DateTime $time      Time into the day to run the task
     * @param Job      $task      Task object
     */
    public function __construct(int $frequency, DateTime $time, Job $task)
    {
        $this->frequency              = $frequency;
        $this->time                   = $time;
        $this->job                    = $task;
        $this->arguments              = '';
        $this->createdDate            = new DateTime();
        $this->taskScheduledRunResult = new ArrayCollection();

        $this->enable();
    }

    /**
     * Get queue
     *
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Set queue
     *
     * @param string $queue queue
     *
     * @return void
     */
    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    /**
     * Returns associated job object
     *
     * @return Job
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * Returns initial time to start the job at first
     *
     * @return DateTime
     */
    public function getTime(): DateTime
    {
        return $this->time;
    }

    /**
     * Returns is enabled flag
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Sets on the enable flag
     *
     * @return void
     */
    public function enable(): void
    {
        $this->isEnabled = true;
    }

    /**
     * Sets off the enabled flag
     *
     * @return void
     */
    public function disable(): void
    {
        $this->isEnabled = false;
    }

    /**
     * Returns the date when this job was scheduled
     *
     * @return DateTime
     */
    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    /**
     * Returns date and time of the first time when the job had to be started off
     *
     * @return DateTime
     */
    public function getFirstRunDateTime(): DateTime
    {
        $createdDate = $this->getCreatedDate()->format('Ymd');
        $time        = $this->getTime()->format('Hi');

        return \DateTime::createFromFormat('YmdHi', $createdDate . $time);
    }

    /**
     * Returns the time when this schedule was updated
     *
     * @return DateTime
     */
    public function getUpdatedDate(): DateTime
    {
        return $this->updatedDate;
    }

    /**
     * Sets the time when this schedule was last time updated
     *
     * @param DateTime $updatedDate The date
     *
     * @return void
     */
    public function setUpdatedDate(DateTime $updatedDate): void
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * Return schedule executions
     *
     * @return JobScheduleExecution[]
     */
    public function getTaskScheduledRunResult(): array
    {
        return $this->taskScheduledRunResult->toArray();
    }

    /**
     * Pre flush event handler
     *
     * @return void
     * @ORM\PreFlush()
     */
    public function preFlush(): void
    {
        $this->updatedDate = new DateTime();
    }

}