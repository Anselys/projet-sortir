<?php

namespace App\Scheduler\Handler;
namespace App\Scheduler\Handler;

use App\Repository\SortieRepository;
use App\Scheduler\Message\ArchiveScheduler;
use DateTime;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ArchiveHandler
{
    public function __invoke(ArchiveScheduler $message, SortieRepository $sortieRepository): void
    {
        $sortiesArchiveesToday = $sortieRepository->archiverSorties();
    }
}

