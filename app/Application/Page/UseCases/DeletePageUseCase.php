<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\Repositories\PageRepositoryInterface;

class DeletePageUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
