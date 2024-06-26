<?php

namespace App\Kobo\Request;

class Bookmark
{
    public ?int $contentSourceProgressPercent = null;
    public ?\DateTime $lastModified = null;
    public ?ReadingStateLocation $location = null;

    public ?int $progressPercent = null;
}
