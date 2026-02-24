<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManager
{
    public function __construct(private SluggerInterface $slugger) {}

    public function upload(UploadedFile $file, string $dir, string $basicName, ?string $oldFileToDelete = null): string
    {
        $newName = sprintf('%s-%s.%s', $this->slugger->slug($basicName), uniqid(), $file->guessExtension());
        $file->move($dir, $newName);
        if ($oldFileToDelete && file_exists($dir.'/'.$oldFileToDelete)) {
            unlink($dir.'/'.$oldFileToDelete);
        }

        return $newName;
    }

}
