<?php

namespace App\Domain\Page\Entities;

class PageEntity
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $status = null,
    ) {}
}
