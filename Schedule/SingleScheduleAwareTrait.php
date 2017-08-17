<?php


namespace Faecie\ScheduleBundle\Schedule;

/**
 * Trait implements ScheduleAwareInterface for services that require only one schedule
 *
 * @see ScheduleAwareInterface
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 */
trait SingleScheduleAwareTrait
{
    /**
     * Schedule service
     *
     * @var ScheduleService
     */
    protected $schedule;

    /**
     * {@inheritdoc}
     */
    public function setSchedule(ScheduleService $schedule, $name)
    {
        $this->schedule = $schedule;
    }
}