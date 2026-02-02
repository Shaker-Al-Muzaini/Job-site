<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\DTOs\PageData;
use App\Domain\Page\Repositories\PageRepositoryInterface;

class CreatePageUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(array $payload)
    {
        $data = PageData::fromArray($payload);
        return $this->repo->create($data);
    }
}
