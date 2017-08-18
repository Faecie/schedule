<?php
/**
 * Schedule
 *
 * @copyright Copyright (c) 2016-2017, Umyarov Ruslan <umyarovrr@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Faecie\ScheduleBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScheduleTask
 *
 * @author Umyarov Ruslan <umyarovrr@gmail.com>
 * @internal
 */
class ScheduleCommand extends AbstractScheduleCommand
{
    /**
     * @inheritDoc
     */
    public function configure()
    {
        $this->setName('faecie:schedule:wrapper');
    }

    public function getDescription()
    {
        return 'Command that wraps given task to run in a schedule';
    }

    /**
     * {{@inheritdoc}}
     */
    protected function executeCommand(InputInterface $input, OutputInterface $output)
    {
        /** @var Command $command */
        $commandInput = new ArrayInput($this->execution->getJobSchedule()->getArguments());
        $commandClass = $this->execution->getJobSchedule()->getJob()->getClassName();
        $command      = new $commandClass();

        if ($input->isInteractive()) {
            $output->writeln(
                sprintf(
                    'Starting task %s iteration %d',
                    $this->execution->getJobSchedule()->getJob()->getSystemName(),
                    $this->execution->getId()
                )
            );
        }

        $command->run($commandInput, $output);
    }
}