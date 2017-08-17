<?php
/**
 * Alfa Capital Holdings (Cyprus) Limited.
 *
 * The following source code is PROPRIETARY AND CONFIDENTIAL. Use of this source code
 * is governed by the Alfa Capital Holdings (Cyprus) Ltd. Non-Disclosure Agreement
 * previously entered between you and Alfa Capital Holdings (Cyprus) Limited.
 *
 * By accessing, using, copying, modifying or distributing this software, you acknowledge
 * that you have been informed of your obligations under the Agreement and agree
 * to abide by those obligations.
 *
 * @author Ruslan Umyarov <ruslan.umyarov@alfaforex.com>
 */
declare(strict_types = 1);

namespace Faecie\ScheduleBundle\Enum;

/**
 * Class ExecutionStateEnum
 */
class ExecutionStateEnum
{
    /**
     * State of operation which did not run
     */
    const NOT_STARTED = 0;

    /**
     * State of operation which did not run
     */
    const QUEUED = 1;

    /**
     * State of operation that is executing now
     */
    const IS_RUNNING = 2;

    /**
     * State of operation that was finished with success
     */
    const SUCCESS = 3;

    /**
     * State of operation that was finished with failure
     */
    const FAILURE = 4;
}
