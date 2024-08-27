<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeFlutterCommand extends Command
{
    protected $signature = 'make:flutter {name} {--seeder}';

    protected $description = 'Creates files for a Flutter component in Laravel.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $createSeeder = $this->option('seeder');

        $this->createResource($name);
        $this->createMigration($name);
        $this->createHelper($name);
        $this->createModel($name);
        $this->createController($name);
        $this->updateRoutes($name);

        if ($createSeeder) {
            $this->createSeeder($name);
        }

        $this->info('All necessary files have been created or were already present!');
    }
    private function createSeeder($name)
    {
        $directory = database_path("seeders/");
        $className = "{$name}Seeder";
        $filename = "{$className}.php";

        $content = "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Seeder;\nuse App\Models\\{$name};\n\nclass {$className} extends Seeder\n{\n    public function run()\n    {\n        // Add your seeding logic here\n        {$name}::factory()->count(10)->create();\n    }\n}";

        $this->createFile($directory, $filename, $name, 'Seeder', $content);
        $this->updateDatabaseSeeder($name);

    }

    private function updateDatabaseSeeder($name)
    {
        $seederClass = "{$name}Seeder::class";
        $databaseSeederFile = database_path('seeders/DatabaseSeeder.php');
        if (File::exists($databaseSeederFile)) {
            $fileContents = File::get($databaseSeederFile);
            // Check if $this->call([]) exists
            if (strpos($fileContents, '$this->call([') === false) {
                // If it doesn't exist, log a warning
                $this->info("Cannot add seeder to DatabaseSeeder.php automatically. Please add '{$seederClass}' to the \$this->call([]) array manually.");
                return;
            }

            // If $this->call([]) exists, add the seeder class if not already present
            $needle = '$this->call([';
            if (strpos($fileContents, $seederClass) === false) {
                $replacement = "$needle\n            {$seederClass},";
                $newFileContents = str_replace($needle, $replacement, $fileContents);
                File::put($databaseSeederFile, $newFileContents);
                $this->info("Seeder '{$name}Seeder' added to DatabaseSeeder.php.");
            } else {
                $this->comment("Seeder '{$name}Seeder' already exists in DatabaseSeeder.php, skipping...");
            }
        } else {
            $this->error("DatabaseSeeder.php not found.");
        }
    }





    private function createResource($name)
    {
        $directory = app_path("Http/Resources/");
        $filename = "{$name}Resource.php";

        $this->createFile($directory, $filename, $name, 'Resource', "<?php\n\nnamespace App\Http\Resources;\n\nuse Illuminate\Http\Request;\nuse Illuminate\Http\Resources\Json\JsonResource;\n\nclass {$name}Resource extends JsonResource\n{\n    /**\n     * Transform the resource into an array.\n     *\n     * @return array<string, mixed>\n     */\n    public function toArray(Request \$request): array\n    {\n        \$data = parent::toArray(\$request);\n\n        array_walk_recursive(\$data, function (&\$item) {\n            \$item = \$item === null ? '' : \$item;\n        });\n        return \$data;\n    }\n}");
    }

    private function createMigration($name)
    {
        $directory = database_path("migrations/");
        $tableName = Str::snake(Str::plural($name)); // Correct table name
        $existingMigration = $this->findExistingMigration($directory, $tableName);

        if ($existingMigration) {
            $this->comment("Migration '{$existingMigration}' already exists, skipping...");
        } else {
            $timestamp = date('Y_m_d_His');
            $filename = "{$timestamp}_create_{$tableName}_table.php";
            $content = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nreturn new class extends Migration\n{\n    public function up()\n    {\n        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
            $content .= "            \$table->id();\n";
            $content .= "            \$table->timestamps();\n";
            $content .= "        });\n";
            $content .= "    }\n\n    public function down()\n    {\n";
            $content .= "        Schema::dropIfExists('{$tableName}');\n";
            $content .= "    }\n};\n";

            $this->createFile($directory, $filename, $name, 'Migration', $content);
        }
    }


    private function findExistingMigration($directory, $migrationName)
    {
        $files = File::files($directory);
        foreach ($files as $file) {
            if (Str::contains($file->getFilename(), $migrationName)) {
                return $file->getFilename();
            }
        }
        return null;
    }

    private function createHelper($name)
    {
        $directory = app_path("Helpers/");
        $filename = "{$name}Helper.php";

        $this->createFile($directory, $filename, $name, 'Helper', "<?php\n\nnamespace App\Helpers;\n\nfunction {$name}Helper()\n{\n     \n}");
    }

    private function createModel($name)
    {
        $directory = app_path("Models/");
        $filename = "{$name}.php";

        $content = "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;\n\nclass {$name} extends Model\n{\n    use SoftDeletes;\n\n    protected \$table = '" . Str::snake(Str::plural($name)) . "';\n\n    protected \$guarded = [];\n}";

        $this->createFile($directory, $filename, $name, 'Model', $content);
    }


    private function createController($name)
    {
        $directory = app_path("Http/Controllers/Api/");
        $filename = "{$name}Controller.php";

        $this->createFile($directory, $filename, $name, 'Controller', "<?php\n\nnamespace App\Http\Controllers\Api;\n\nuse App\Http\Controllers\Controller;\nuse App\Http\Resources\\{$name}Resource;\nuse App\Models\\{$name};\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\Log;\n\nclass {$name}Controller extends Controller\n{\n  public function json()\n    {\n        try {\n            Log::info('Fetching all {$name}s in JSON format');\n            \$models = {$name}::all();\n            return {$name}Resource::collection(\$models);\n        } catch (\Exception \$ex) {\n            Log::error('Failed to fetch all {$name}s in JSON format: ' . \$ex->getMessage());\n            return response()->json([\n                'success' => false,\n                'message' => \$ex->getMessage(),\n            ], 500);\n        }\n    }\n  public function show({$name} \$model)\n    {\n        try {\n            Log::info('Fetching {$name} with ID: ' . \$model->id);\n            return new {$name}Resource(\$model);\n        } catch (\Exception \$ex) {\n            Log::error('Failed to fetch {$name} with ID ' . \$model->id . ': ' . \$ex->getMessage());\n            return response()->json([\n                'success' => false,\n                'message' => \$ex->getMessage(),\n            ], 500);\n        }\n    }\n\n    public function store(Request \$request)\n    {\n        try {\n            Log::info('Storing {$name}');\n\n            \$validatedData = \$request->validate([\n                // Todo: adapt the columns\n             ]);\n\n            \$model = {$name}::create(\$validatedData);\n\n            \$responseData = [\n                'success' => true,\n                'data' => new {$name}Resource(\$model)\n            ];\n\n            return response()->json(\$responseData);\n\n        } catch (\Illuminate\Validation\ValidationException \$ex) {\n            Log::error('Validation failed while storing {$name}: ' . implode(', ', \$ex->errors()));\n            return response()->json([\n                'success' => false,\n                'message' => 'Validation failed',\n                'errors' => \$ex->errors()\n            ], 422);\n        } catch (\Exception \$ex) {\n            Log::error('Failed to store {$name}: ' . \$ex->getMessage());\n            return response()->json([\n                'success' => false,\n                'message' => \$ex->getMessage(),\n            ], 500);\n        }\n    }\n\n    public function update(Request \$request, {$name} \$model)\n    {\n        try {\n            Log::info('Updating {$name} with ID: ' . \$model->id);\n\n            \$validatedData = \$request->validate([\n                // Todo: adapt the columns\n            ]);\n\n            \$model->update(\$validatedData);\n\n            \$responseData = [\n                'success' => true,\n                'data' => new {$name}Resource(\$model)\n            ];\n\n            return response()->json(\$responseData);\n\n        } catch (\Illuminate\Validation\ValidationException \$ex) {\n            Log::error('Validation failed while updating {$name} with ID ' . \$model->id . ': ' . implode(', ', \$ex->errors()));\n            return response()->json([\n                'success' => false,\n                'message' => 'Validation failed',\n                'errors' => \$ex->errors()\n            ], 422);\n        } catch (\Exception \$ex) {\n            Log::error('Failed to update {$name} with ID ' . \$model->id . ': ' . \$ex->getMessage());\n            return response()->json([\n                'success' => false,\n                'message' => \$ex->getMessage(),\n            ], 500);\n        }\n    }\n\n    public function destroy({$name} \$model)\n    {\n        try {\n            Log::info('Deleting {$name} with ID: ' . \$model->id);\n            \$model->delete();\n            return response()->json([\n                'success' => true\n            ], 202);\n        } catch (\Exception \$ex) {\n            Log::error('Failed to delete {$name} with ID ' . \$model->id . ': ' . \$ex->getMessage());\n            return response()->json([\n                'success' => false,\n                'message' => \$ex->getMessage(),\n            ], 500);\n        }\n    }\n\n   }");
    }



    private function updateRoutes($name)
    {
        $routesFile = base_path('routes/api.php');
        $controller = "{$name}Controller";
        $prefix = Str::plural(Str::kebab($name));
        $namespace = "App\Http\Controllers\Api\\";

        $routeDefinition = "\nRoute::prefix('{$prefix}')\n";
        $routeDefinition .= "->controller({$namespace}{$controller}::class)\n";
        $routeDefinition .= "->group(function () {\n";
        $routeDefinition .= "    Route::get('/json', 'json');\n";
        $routeDefinition .= "    Route::get('/{id}', 'show');\n";
        $routeDefinition .= "    Route::post('/', 'store');\n";
        $routeDefinition .= "    Route::put('/{id}', 'update');\n";
        $routeDefinition .= "    Route::delete('/{id}', 'destroy');\n";
        $routeDefinition .= "});\n";

        if (File::exists($routesFile)) {
            $fileContents = File::get($routesFile);

            $importPattern = "/^use Illuminate\\\\Support\\\\Facades\\\\Route;/m";
            if (!preg_match($importPattern, $fileContents)) {
                File::prepend($routesFile, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
                $this->info("Route facade imported to api.php.");
            }

            $prefixPattern = "/Route::prefix\\('{$prefix}'\\)/";

            if (preg_match($prefixPattern, $fileContents)) {
                $this->comment("Routes with prefix '{$prefix}' already exist in api.php, skipping...");
            } else {
                File::append($routesFile, $routeDefinition);
                $this->info("Routes for '{$name}' added to api.php.");
            }
        } else {
            $this->error("Routes file not found.");
        }
    }




    private function createFile($directory, $filename, $name, $type, $content)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($directory . $filename)) {
            $this->comment("{$type} '{$filename}' already exists, skipping...");
        } else {
            File::put($directory . $filename, $content);
            $this->info("{$type} '{$filename}' created successfully.");
        }
    }
}
