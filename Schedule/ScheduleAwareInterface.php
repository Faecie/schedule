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

/**
 * Interface ScheduleAwareInterface
 */
interface ScheduleAwareInterface
{
    /**
     * Sets schedule service
     *
     * @param ScheduleService $schedule Service Schedule service
     * @param string          $name     Unique name of the schedule
     *
     * @return void
     */
    public function setSchedule(ScheduleService $schedule, $name);
}