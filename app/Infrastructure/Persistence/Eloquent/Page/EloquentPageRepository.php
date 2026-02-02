<?php

namespace App\Infrastructure\Persistence\Eloquent\Page;

use App\Domain\Page\DTOs\PageData;
use App\Domain\Page\Repositories\PageRepositoryInterface;
use App\Domain\Page\ValueObjects\PageSearchCriteria;
use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPageRepository implements PageRepositoryInterface
{
    public function paginate(PageSearchCriteria $criteria): LengthAwarePaginator
    {
        return Page::query()
            ->when($criteria->search, function ($q) use ($criteria) {
                $q->where('name', 'LIKE', "%{$criteria->search}%")
                  ->orWhere('description', 'LIKE', "%{$criteria->search}%");
            })
            ->orderBy($criteria->sortBy, $criteria->sortDir)
            ->paginate($criteria->limit);
    }

    public function find(int $id): ?Page
    {
        return Page::find($id);
    }

    public function create(PageData $data): Page
    {
        return Page::create($data->toArray());
    }

    public function update(int $id, PageData $data): ?Page
    {
        $model = Page::find($id);
        if (! $model) {
            return null;
        }

        $model->update($data->toArray());
        return $model;
    }

    public function delete(int $id): bool
    {
        $model = Page::find($id);
        if (! $model) {
            return false;
        }

        return (bool) $model->delete();
    }
}
