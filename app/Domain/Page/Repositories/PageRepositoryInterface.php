<?php

namespace App\Domain\Page\Repositories;

use App\Domain\Page\DTOs\PageData;
use App\Domain\Page\ValueObjects\PageSearchCriteria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface PageRepositoryInterface
{
    public function paginate(PageSearchCriteria $criteria): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function create(PageData $data): Model;

    public function update(int $id, PageData $data): ?Model;

    public function delete(int $id): bool;
}
