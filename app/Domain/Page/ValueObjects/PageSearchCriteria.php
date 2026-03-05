<?php

namespace App\Domain\Page\ValueObjects;

class PageSearchCriteria
{
    public function __construct(
        public ?string $search = null,
        public string $sortBy = 'id',
        public string $sortDir = 'desc',
        public int $limit = 10,
    ) {}
}
