<?php

namespace App\Scheduler;

use App\Message\Archive;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class ArchiveProvider implements ScheduleProviderInterface
{
    public function __construct()
    {
    }
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::every('24 hours', new Archive('sorties archivées')));
    }
}
