<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class ArchiveProvider implements ScheduleProviderInterface
{

    public function getSchedule(): Schedule
    {
        RecurringMessage::cron('@daily', 'archive');
    }
}
