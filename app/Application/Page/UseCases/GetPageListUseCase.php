<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\Repositories\PageRepositoryInterface;
use App\Domain\Page\ValueObjects\PageSearchCriteria;

class GetPageListUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(PageSearchCriteria $criteria): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repo->paginate($criteria);
    }
}
