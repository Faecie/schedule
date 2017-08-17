<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Faecie\ScheduleBundle\Enum;

/**
 * Class TaskScheduleRunResultStatusEnum
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 */
class ExecutionStateMessageEnum
{
    /**
     * Message about operation which did not run
     */
    const NOT_STARTED = 'Execution not started';

    /**
     * Message denotes that execution was queued
     */
    const QUEUED = 'Execution is in the queue';

    /**
     * Message for task that is executing now
     */
    const IS_RUNNING = 'Running...';

    /**
     * Message for task that was finished with success
     */
    const SUCCESS = 'Success';

    /**
     * Message for task that was finished with failure
     */
    const FAILURE = 'FAILURE';
}