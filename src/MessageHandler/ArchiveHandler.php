<?php

namespace App\MessageHandler;

use App\Message\Archive;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ArchiveHandler
{
    public function __construct(private LoggerInterface $logger, private SortieRepository $sortieRepository, private EntityManagerInterface $em)
    {
    }

    public function __invoke(Archive $message): void
    {
        $this->sortieRepository->archiverSorties($this->em);
        $this->logger->warning($message->message);
    }
}
