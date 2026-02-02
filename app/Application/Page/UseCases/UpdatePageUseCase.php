<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\DTOs\PageData;
use App\Domain\Page\Repositories\PageRepositoryInterface;

class UpdatePageUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(int $id, array $payload)
    {
        $data = PageData::fromArray($payload);
        return $this->repo->update($id, $data);
    }
}
