<?php

namespace App\Message;


use App\Repository\SortieRepository;

final class Archive
{
    public function __construct(public string $message)
    {

    }
}
