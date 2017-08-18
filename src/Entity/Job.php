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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Console\Command\Command;

/**
 * Class Report
 *
 * @author Ruslan Umyarov <umyarovrr@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="job")
 * @ORM\HasLifecycleCallbacks()
 */
class Job
{
    /**
     * PK
     *
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * Scheduled tasks list
     *
     * @var ArrayCollection
     * @ORM\OneToMany(
     *   targetEntity="JobSchedule",
     *   mappedBy="job",
     *   indexBy="id"
     *  )
     */
    private $jobSchedule;

    /**
     * The command class FQCN
     *
     * @var string
     *
     * @ORM\Column(name="command_class", type="string")
     */
    private $commandClass;

    /**
     * Job system name
     *
     * @var string
     * @ORM\Column(name="system_name", type="string")
     */
    private $systemName;

    /**
     * The flag whether a Job is enabled
     *
     * @var boolean
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobSchedule = new ArrayCollection();
        $this->isEnabled   = true;
    }


    /**
     * Returns unique identifier of a job
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the FQCN of a associated command
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->commandClass;
    }

    /**
     * Sets the fully qualified name of a command class
     *
     * @param string $command The FQCN of a command
     *
     * @return void
     */
    public function setClassName($command)
    {
        $this->commandClass = $command;
    }

    /**
     * Returns the system name of a task
     *
     * @return string
     */
    public function getSystemName()
    {
        return $this->systemName;
    }

    /**
     * Set system name of a job
     *
     * @param string $systemName System name
     *
     * @return void
     * @see Command::getName()
     */
    public function setSystemName($systemName)
    {
        $this->systemName = $systemName;
    }

    /**
     * Checks whether a job is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     *  Enables the job
     *
     * @return void
     */
    public function enable()
    {
        $this->isEnabled = true;
    }

    /**
     * Disables the job
     *
     * @return void
     */
    public function disable()
    {
        $this->isEnabled = false;
    }
}