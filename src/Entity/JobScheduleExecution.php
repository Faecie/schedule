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
use Doctrine\ORM\Mapping as ORM;
use Faecie\ScheduleBundle\Enum\ExecutionStateEnum;
use Faecie\ScheduleBundle\Enum\ExecutionStateMessageEnum;

/**
 * Class JobScheduleExecution
 *
 * @author Ruslan Umyarov <umyarovrr@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="job_schedule_execution")
 */
class JobScheduleExecution extends AbstractJobSchedule
{
    /**
     * Job object
     *
     * @var JobSchedule
     * @ORM\ManyToOne(targetEntity="JobSchedule")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private $jobSchedule;

    /**
     * The number of execution of the job
     *
     * @var integer
     * @ORM\Column(name="execution_number", type="integer")
     */
    private $executionNumber;

    /**
     * Planned date of execution
     *
     * @var \DateTime
     * @ORM\Column(name="scheduled_date", type="datetime")
     */
    private $scheduledDate;

    /**
     * Current state of this execution
     *
     * @var int
     * @ORM\Column(name="state", type="smallint")
     *
     * @see ExecutionStateEnum
     */
    private $state;

    /**
     * Time when the execution was started
     *
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * Time when the execution was finished
     *
     * @var \DateTime
     * @ORM\Column(name="finish_date", type="datetime", nullable="true")
     */
    private $finishDate;

    /**
     * The last result information
     *
     * @var string
     * @ORM\Column(name="last_result_info", type="string")
     */
    private $lastResultInfo;

    /**
     * FQCN of the class which is factually runs the execution
     *
     * @var string
     * @ORM\Column(name="command_class", type="string")
     */
    private $commandClass;

    /**
     * JobScheduleExecution constructor
     *
     * @param JobSchedule $jobSchedule Schedule of the job
     * @param string|null $command     System FQCN of concrete class that runs the execution
     */
    public function __construct($jobSchedule, $command = null)
    {
        $this->jobSchedule     = $jobSchedule;
        $this->startDate       = new \DateTime();
        $this->executionNumber = $jobSchedule->calculateNextExecutionNumber();
        $this->scheduledDate   = $jobSchedule->getNextRunDate();
        $this->commandClass    = $command ?: $jobSchedule->getJob()->getClassName();

        $this->setArguments($jobSchedule->getArguments());
        $this->setState(ExecutionStateEnum::NOT_STARTED);
        $this->setLastResultInfo(ExecutionStateMessageEnum::NOT_STARTED);
    }

    /**
     * Sets operation state
     *
     * @param int $state One of the ExecutionStateEnum constants
     *
     * @return void
     * @see ExecutionStateEnum
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get systemClass
     *
     * @return string
     */
    public function getCommandClass()
    {
        return $this->commandClass;
    }

    /**
     * Returns associated JobSchedule object
     *
     * @return JobSchedule
     */
    public function getJobSchedule()
    {
        return $this->jobSchedule;
    }

    /**
     * Returns the number of execution
     *
     * @return integer
     */
    public function getExecutionNumber()
    {
        return $this->executionNumber;
    }

    /**
     * Returns the flag whether execution was successful or not
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->state === ExecutionStateEnum::SUCCESS;
    }

    /**
     * Returns the time when this execution was started
     *
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Get date and time of when to run this execution. May differ from factual start date
     *
     * @return DateTime
     */
    public function getScheduleDateTime()
    {
        return $this->scheduledDate;
    }

    /**
     * Returns the time when this execution was finished
     *
     * @return DateTime|null
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * Sets finish date for the running job
     *
     * @param DateTime $finishDate Finish date
     *
     * @return void
     */
    public function setFinishDate(DateTime $finishDate)
    {
        $this->finishDate = $finishDate;
    }

    /**
     * Returns last information about execution
     *
     * @return string
     */
    public function getLastResultInfo()
    {
        return $this->lastResultInfo;
    }

    /**
     * Sets last information flashed before exit operation
     *
     * @param string $lastResultInfo Message string
     *
     * @return void
     */
    public function setLastResultInfo($lastResultInfo)
    {
        $this->lastResultInfo = $lastResultInfo;
    }
}