<?php

namespace App\Presentation\Http\Controllers\Page;

use App\Application\Page\UseCases\CreatePageUseCase;
use App\Application\Page\UseCases\DeletePageUseCase;
use App\Application\Page\UseCases\GetPageDetailUseCase;
use App\Application\Page\UseCases\GetPageListUseCase;
use App\Application\Page\UseCases\UpdatePageUseCase;
use App\Domain\Page\ValueObjects\PageSearchCriteria;
use App\Presentation\Http\Requests\Page\PageIndexRequest;
use App\Presentation\Http\Requests\Page\PageStoreRequest;
use App\Presentation\Http\Requests\Page\PageUpdateRequest;
use App\Presentation\Http\Resources\Page\PageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageController
{
    public function index(PageIndexRequest $request, GetPageListUseCase $useCase): JsonResponse
    {
        $criteria = new PageSearchCriteria(
            search: $request->get('search'),
            sortBy: $request->get('sort_by', 'id'),
            sortDir: $request->get('sort_dir', 'desc'),
            limit: (int) $request->get('limit', 10),
        );

        $items = $useCase->execute($criteria);

        return response()->json([
            'status' => 'success',
            'data' => PageResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(int $id, GetPageDetailUseCase $useCase): JsonResponse
    {

        $item = $useCase->execute($id);

        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Page not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new PageResource($item),
        ]);
    }

    public function store(PageStoreRequest $request, CreatePageUseCase $useCase): JsonResponse
    {

        DB::beginTransaction();

        try {
            $item = $useCase->execute($request->validated());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => new PageResource($item),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Page store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function update(int $id, PageUpdateRequest $request, UpdatePageUseCase $useCase): JsonResponse
    {
        DB::beginTransaction();

        try {
            $item = $useCase->execute($id, $request->validated());

            if (! $item) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Page not found',
                ], 404);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => new PageResource($item),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Page update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function destroy(int $id, DeletePageUseCase $useCase): JsonResponse
    {
        $deleted = $useCase->execute($id);

        if (! $deleted) {
            return response()->json(['status' => 'error', 'message' => 'Page not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Page deleted successfully',
        ]);
    }
}
