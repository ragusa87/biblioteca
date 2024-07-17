<?php

namespace App\Service;

use App\Entity\Book;

class BookFormatStorage implements BookFormatStorageInterface
{
    public function __construct(private string $storageDir)
    {
    }

    protected function getLocation(Book $book, string $format): string
    {
        return $this->storageDir.'/'.substr($book->getUuid(), 0, 3).'/'.$book->getUuid().'.'.$format;
    }

    public function store(Book $book, string $format, \SplFileInfo $file): void
    {
        $filename = $this->getLocation($book, $format);
        $directory = dirname($filename);
        if (false === is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        rename($file->getPathname(), $filename);
    }

    public function has(Book $book, string $format): bool
    {
        return file_exists($this->getLocation($book, $format));
    }

    public function remove(Book $book, string $format): void
    {
        if (false === unlink($this->getLocation($book, $format))) {
            throw new \RuntimeException('Could not remove the file');
        }
    }

    public function get(Book $book, string $format): \SplFileInfo
    {
        return new \SplFileInfo($this->getLocation($book, $format));
    }
}
