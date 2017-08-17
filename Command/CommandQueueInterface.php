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

namespace Faecie\ScheduleBundle\Command;

use Symfony\Component\Console\Command\Command;

/**
 * Interface JobQueueInterface
 */
interface CommandQueueInterface
{
    /**
     * Put command into the queue
     *
     * @param string $class     FQCN of Symfony's Command that is going to be queued
     * @param array  $arguments Arguments provided with queued command
     *
     * @return void
     * @see Command
     */
    public function pushCommand(string $class, array $arguments): void;
}