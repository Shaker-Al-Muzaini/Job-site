<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\Repositories\PageRepositoryInterface;

class GetPageDetailUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(int $id): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->repo->find($id);
    }
}
