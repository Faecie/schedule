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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Class AbstractTaskSchedule
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * @MappedSuperclass()
 */
abstract class AbstractJobSchedule
{
    /**
     * PK
     *
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id",type="integer")
     */
    protected $id;

    /**
     * Arguments to run with this task
     *
     * @var string
     * @ORM\Column(name="args", type="string")
     */
    protected $arguments;

    /**
     * Frequency of running, in minutes
     *
     * @var integer
     * @ORM\Column(name="frequency", type="integer")
     */
    protected $frequency;

    /**
     * Returns the id of a task
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns frequency with which to run the task
     *
     * @return integer
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Get arguments
     *
     * @return array arguments indexed by name
     */
    public function getArguments()
    {
        return json_decode($this->arguments, true) ?: [];
    }

    /**
     * setArguments
     *
     * @param array $arguments Arguments
     *
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = json_encode($arguments);
    }
}
