<?php

namespace App\Service;

use App\Entity\Book;

interface BookFormatStorageInterface
{
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_EPUB_KOBO = 'kobo.epub';

    public function store(Book $book, string $format, \SplFileInfo $file): void;

    public function has(Book $book, string $format): bool;

    public function remove(Book $book, string $format): void;

    public function get(Book $book, string $format): \SplFileInfo;
}
