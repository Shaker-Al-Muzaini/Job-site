<?php

namespace App\Application\Page\UseCases;

use App\Domain\Page\Repositories\PageRepositoryInterface;
use App\Domain\Page\ValueObjects\PageSearchCriteria;

class GetPageUseCase
{
    public function __construct(private PageRepositoryInterface $repo) {}

    public function execute(PageSearchCriteria $criteria)
    {
        return $this->repo->getAll([
            'search'   => $criteria->search,
            'sort_by'  => $criteria->sortBy,
            'sort_dir' => $criteria->sortDir,
            'limit'    => $criteria->limit,
        ]);
    }
}
