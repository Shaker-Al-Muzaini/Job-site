<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeDomainCommand extends Command
{
    protected $signature = 'make:domain {name}';
    protected $description = 'Create full DDD + Clean Architecture domain (model, migration, use cases, repo, controller, requests, resources)';

    public function handle()
    {
        $name      = ucfirst($this->argument('name'));   // Page / Book / Order
        $plural    = str($name)->pluralStudly();         // Pages / Books / Orders
        $snake     = str($name)->snake();                // page
        $snakePlural = str($name)->snake()->plural();    // pages
        $tableName = $snakePlural;                       // pages

        $this->info("🚀 Generating domain: {$name}");

        // 1) Create base directories
        $this->createDirectories($name);

        // 2) Generate Model + Migration + Factory + Seeder
        $this->generateModelAndMigration($name, $tableName);

        // 3) Generate Domain/Application/Infrastructure/Presentation files
        $this->generateDomainFiles($name);
        $this->generateApplicationFiles($name);
        $this->generateInfrastructureFiles($name);
        $this->generatePresentationFiles($name);

        // 4) Append API route
        $this->appendApiRoute($name, $snakePlural);

        $this->info("\n🎉 Domain '{$name}' generated successfully with full Enterprise structure!");
        return Command::SUCCESS;
    }

    private function createDirectories(string $name): void
    {
        $dirs = [
            "app/Domain/{$name}/Entities",
            "app/Domain/{$name}/Repositories",
            "app/Domain/{$name}/ValueObjects",
            "app/Domain/{$name}/DTOs",

            "app/Application/{$name}/UseCases",

            "app/Infrastructure/Persistence/Eloquent/{$name}",

            "app/Presentation/Http/Controllers/{$name}",
            "app/Presentation/Http/Requests/{$name}",
            "app/Presentation/Http/Resources/{$name}",
        ];

        foreach ($dirs as $dir) {
            File::ensureDirectoryExists($dir, 0777, true);
            $this->line("  📁 Created: {$dir}");
        }
    }

    private function generateModelAndMigration(string $name, string $tableName): void
    {
        $this->info("\n📦 Generating Model, Migration, Factory, Seeder ...");

        // Model + Migration + Factory + Seeder
        Artisan::call('make:model', [
            'name' => "Models/{$name}",
            '-m'   => true, // migration
            '-f'   => true, // factory
            '-s'   => true, // seeder
        ]);

        $this->line(Artisan::output());

        // تعديل سريع للموديل ليكون في namespace App\Models
        $modelPath = app_path("Models/{$name}.php");
        if (File::exists($modelPath)) {
            $content = File::get($modelPath);
            // نضيف بعض الـ fillable الافتراضية
            $content = preg_replace(
                '/class '.$name.' extends Model(.*)\R\{/',
                "class {$name} extends Model\n{\n    protected \$fillable = [\n        'name',\n        'description',\n        'status',\n    ];\n",
                $content
            );
            File::put($modelPath, $content);
            $this->line("  ✏️ Updated model: {$modelPath}");
        }
    }

    /* ==================== DOMAIN LAYER ==================== */

    private function generateDomainFiles(string $name): void
    {
        $this->info("\n🏛 Generating Domain layer ...");

        // Entity
        File::put("app/Domain/{$name}/Entities/{$name}Entity.php", "<?php

namespace App\Domain\\{$name}\Entities;

class {$name}Entity
{
    public function __construct(
        public int \$id,
        public string \$name,
        public ?string \$description = null,
        public ?string \$status = null,
    ) {}
}
");

        // DTO
        File::put("app/Domain/{$name}/DTOs/{$name}Data.php", "<?php

namespace App\Domain\\{$name}\DTOs;

class {$name}Data
{
    public function __construct(
        public string \$name,
        public ?string \$description = null,
        public ?string \$status = null,
    ) {}

    public static function fromArray(array \$data): self
    {
        return new self(
            name: \$data['name'] ?? '',
            description: \$data['description'] ?? null,
            status: \$data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => \$this->name,
            'description' => \$this->description,
            'status'      => \$this->status,
        ];
    }
}
");

        // Search Criteria
        File::put("app/Domain/{$name}/ValueObjects/{$name}SearchCriteria.php", "<?php

namespace App\Domain\\{$name}\ValueObjects;

class {$name}SearchCriteria
{
    public function __construct(
        public ?string \$search = null,
        public string \$sortBy = 'id',
        public string \$sortDir = 'desc',
        public int \$limit = 10,
    ) {}
}
");

        // Repository Interface
        File::put("app/Domain/{$name}/Repositories/{$name}RepositoryInterface.php", "<?php

namespace App\Domain\\{$name}\Repositories;

use App\Domain\\{$name}\DTOs\\{$name}Data;
use App\Domain\\{$name}\ValueObjects\\{$name}SearchCriteria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface {$name}RepositoryInterface
{
    public function paginate({$name}SearchCriteria \$criteria): LengthAwarePaginator;

    public function find(int \$id): ?Model;

    public function create({$name}Data \$data): Model;

    public function update(int \$id, {$name}Data \$data): ?Model;

    public function delete(int \$id): bool;
}
");

        $this->line("  ✅ Domain files generated");
    }

    /* ==================== APPLICATION LAYER ==================== */

    private function generateApplicationFiles(string $name): void
    {
        $this->info("\n⚙️ Generating Application (UseCases) ...");

        // Index
        File::put("app/Application/{$name}/UseCases/Get{$name}ListUseCase.php", "<?php

namespace App\Application\\{$name}\UseCases;

use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;
use App\Domain\\{$name}\ValueObjects\\{$name}SearchCriteria;

class Get{$name}ListUseCase
{
    public function __construct(private {$name}RepositoryInterface \$repo) {}

    public function execute({$name}SearchCriteria \$criteria)
    {
        return \$this->repo->paginate(\$criteria);
    }
}
");

        // Show
        File::put("app/Application/{$name}/UseCases/Get{$name}DetailUseCase.php", "<?php

namespace App\Application\\{$name}\UseCases;

use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;

class Get{$name}DetailUseCase
{
    public function __construct(private {$name}RepositoryInterface \$repo) {}

    public function execute(int \$id)
    {
        return \$this->repo->find(\$id);
    }
}
");

        // Store
        File::put("app/Application/{$name}/UseCases/Create{$name}UseCase.php", "<?php

namespace App\Application\\{$name}\UseCases;

use App\Domain\\{$name}\DTOs\\{$name}Data;
use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;

class Create{$name}UseCase
{
    public function __construct(private {$name}RepositoryInterface \$repo) {}

    public function execute(array \$payload)
    {
        \$data = {$name}Data::fromArray(\$payload);
        return \$this->repo->create(\$data);
    }
}
");

        // Update
        File::put("app/Application/{$name}/UseCases/Update{$name}UseCase.php", "<?php

namespace App\Application\\{$name}\UseCases;

use App\Domain\\{$name}\DTOs\\{$name}Data;
use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;

class Update{$name}UseCase
{
    public function __construct(private {$name}RepositoryInterface \$repo) {}

    public function execute(int \$id, array \$payload)
    {
        \$data = {$name}Data::fromArray(\$payload);
        return \$this->repo->update(\$id, \$data);
    }
}
");

        // Delete
        File::put("app/Application/{$name}/UseCases/Delete{$name}UseCase.php", "<?php

namespace App\Application\\{$name}\UseCases;

use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;

class Delete{$name}UseCase
{
    public function __construct(private {$name}RepositoryInterface \$repo) {}

    public function execute(int \$id): bool
    {
        return \$this->repo->delete(\$id);
    }
}
");

        $this->line("  ✅ Application UseCases generated");
    }

    /* ==================== INFRASTRUCTURE LAYER ==================== */

    private function generateInfrastructureFiles(string $name): void
    {
        $this->info("\n💾 Generating Infrastructure (Eloquent Repository) ...");

        File::put("app/Infrastructure/Persistence/Eloquent/{$name}/Eloquent{$name}Repository.php", "<?php

namespace App\Infrastructure\Persistence\Eloquent\\{$name};

use App\Domain\\{$name}\DTOs\\{$name}Data;
use App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;
use App\Domain\\{$name}\ValueObjects\\{$name}SearchCriteria;
use App\Models\\{$name};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Eloquent{$name}Repository implements {$name}RepositoryInterface
{
    public function paginate({$name}SearchCriteria \$criteria): LengthAwarePaginator
    {
        return {$name}::query()
            ->when(\$criteria->search, function (\$q) use (\$criteria) {
                \$q->where('name', 'LIKE', \"%{\$criteria->search}%\")
                  ->orWhere('description', 'LIKE', \"%{\$criteria->search}%\");
            })
            ->orderBy(\$criteria->sortBy, \$criteria->sortDir)
            ->paginate(\$criteria->limit);
    }

    public function find(int \$id): ?{$name}
    {
        return {$name}::find(\$id);
    }

    public function create({$name}Data \$data): {$name}
    {
        return {$name}::create(\$data->toArray());
    }

    public function update(int \$id, {$name}Data \$data): ?{$name}
    {
        \$model = {$name}::find(\$id);
        if (! \$model) {
            return null;
        }

        \$model->update(\$data->toArray());
        return \$model;
    }

    public function delete(int \$id): bool
    {
        \$model = {$name}::find(\$id);
        if (! \$model) {
            return false;
        }

        return (bool) \$model->delete();
    }
}
");

        $this->line("  ✅ Infrastructure repository generated");
    }

    /* ==================== PRESENTATION LAYER ==================== */

    private function generatePresentationFiles(string $name): void
    {
        $this->info("\n🖥 Generating Presentation (Controller, Requests, Resource) ...");

        /* Controller */
        File::put("app/Presentation/Http/Controllers/{$name}/{$name}Controller.php", "<?php

namespace App\Presentation\Http\Controllers\\{$name};

use App\Application\\{$name}\UseCases\Get{$name}ListUseCase;
use App\Application\\{$name}\UseCases\Get{$name}DetailUseCase;
use App\Application\\{$name}\UseCases\Create{$name}UseCase;
use App\Application\\{$name}\UseCases\Update{$name}UseCase;
use App\Application\\{$name}\UseCases\Delete{$name}UseCase;
use App\Domain\\{$name}\ValueObjects\\{$name}SearchCriteria;
use App\Presentation\Http\Requests\\{$name}\\{$name}IndexRequest;
use App\Presentation\Http\Requests\\{$name}\\{$name}StoreRequest;
use App\Presentation\Http\Requests\\{$name}\\{$name}UpdateRequest;
use App\Presentation\Http\Resources\\{$name}\\{$name}Resource;
use Illuminate\Http\JsonResponse;

class {$name}Controller
{
    public function index({$name}IndexRequest \$request, Get{$name}ListUseCase \$useCase): JsonResponse
    {
        \$criteria = new {$name}SearchCriteria(
            search: \$request->get('search'),
            sortBy: \$request->get('sort_by', 'id'),
            sortDir: \$request->get('sort_dir', 'desc'),
            limit: (int) \$request->get('limit', 10),
        );

        \$items = \$useCase->execute(\$criteria);

        return response()->json([
            'status' => 'success',
            'data'   => {$name}Resource::collection(\$items),
            'meta'   => [
                'current_page' => \$items->currentPage(),
                'last_page'    => \$items->lastPage(),
                'total'        => \$items->total(),
            ],
        ]);
    }

    public function show(int \$id, Get{$name}DetailUseCase \$useCase): JsonResponse
    {
        \$item = \$useCase->execute(\$id);

        if (! \$item) {
            return response()->json(['status' => 'error', 'message' => '{$name} not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new {$name}Resource(\$item),
        ]);
    }

    public function store({$name}StoreRequest \$request, Create{$name}UseCase \$useCase): JsonResponse
    {
        \$item = \$useCase->execute(\$request->validated());

        return response()->json([
            'status' => 'success',
            'data'   => new {$name}Resource(\$item),
        ], 201);
    }

    public function update(int \$id, {$name}UpdateRequest \$request, Update{$name}UseCase \$useCase): JsonResponse
    {
        \$item = \$useCase->execute(\$id, \$request->validated());

        if (! \$item) {
            return response()->json(['status' => 'error', 'message' => '{$name} not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new {$name}Resource(\$item),
        ]);
    }

    public function destroy(int \$id, Delete{$name}UseCase \$useCase): JsonResponse
    {
        \$deleted = \$useCase->execute(\$id);

        if (! \$deleted) {
            return response()->json(['status' => 'error', 'message' => '{$name} not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => '{$name} deleted successfully',
        ]);
    }
}
");

        /* Requests */
        File::put("app/Presentation/Http/Requests/{$name}/{$name}IndexRequest.php", "<?php

namespace App\Presentation\Http\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class {$name}IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'   => 'nullable|string|max:255',
            'sort_by'  => 'nullable|string|in:id,name,created_at',
            'sort_dir' => 'nullable|string|in:asc,desc',
            'limit'    => 'nullable|integer|min:1|max:100',
        ];
    }
}
");

        File::put("app/Presentation/Http/Requests/{$name}/{$name}StoreRequest.php", "<?php

namespace App\Presentation\Http\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class {$name}StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|string|max:50',
        ];
    }
}
");

        File::put("app/Presentation/Http/Requests/{$name}/{$name}UpdateRequest.php", "<?php

namespace App\Presentation\Http\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class {$name}UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|string|max:50',
        ];
    }
}
");

        /* Resource */
        File::put("app/Presentation/Http/Resources/{$name}/{$name}Resource.php", "<?php

namespace App\Presentation\Http\Resources\\{$name};

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id'          => \$this->id,
            'name'        => \$this->name,
            'description' => \$this->description,
            'status'      => \$this->status,
            'created_at'  => \$this->created_at?->toDateTimeString(),
            'updated_at'  => \$this->updated_at?->toDateTimeString(),
        ];
    }
}
");

        $this->line("  ✅ Presentation layer generated");
    }

    /* ==================== ROUTES ==================== */

    private function appendApiRoute(string $name, string $snakePlural): void
    {
        $this->info("\n🛣  Adding api route ...");

        $controllerFqn = "App\\Presentation\\Http\\Controllers\\{$name}\\{$name}Controller::class";
        $routeLine     = "Route::apiResource('{$snakePlural}', \\App\\Presentation\\Http\\Controllers\\{$name}\\{$name}Controller::class);";

        $apiPath = base_path('routes/api.php');
        $content = File::get($apiPath);

        if (str_contains($content, $routeLine)) {
            $this->line('  ⚠️ Route already exists in routes/api.php');
            return;
        }

        File::append($apiPath, "\n// {$name} domain routes\n{$routeLine}\n");

        $this->line("  ✅ Route appended to routes/api.php");
    }
}
