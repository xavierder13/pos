<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * php artisan make:module Product   (or: make module name=Product from the project root)
 *
 * The backend half of a module, following the starter's conventions: migration,
 * model (audited), factory, permission seeder, Form Requests, Service, API
 * Resource, thin controller, REST routes with `permission:` middleware, and a
 * feature test. Shared files are extended at their `[make:module …]` markers:
 * routes/api.php, PermissionSeeder::MODULES, AuditTrailController::RECORDS and
 * RecordTypes::TYPES (attachments + approvals on its view page).
 * The templates are in stubs/module/ — change them to change every future module.
 */
class MakeModule extends Command
{
    protected $signature = 'make:module {name : Singular StudlyCase name, e.g. Product or SalesOrder} {--force : Overwrite existing files}';

    protected $description = 'Scaffold a backend module (model → routes → test) from stubs/module';

    public function handle(Filesystem $files): int
    {
        $model = Str::studly(Str::singular($this->argument('name')));
        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $model)) {
            $this->error('Use a singular StudlyCase name, e.g. Product or SalesOrder.');

            return self::FAILURE;
        }

        $models = Str::plural($model);
        $kebab = Str::kebab($model);
        $vars = [
            '{{Models}}' => $models,
            '{{Model}}' => $model,
            '{{models}}' => Str::camel($models),
            '{{model}}' => Str::camel($model),
            '{{table}}' => Str::snake($models),
            '{{kebabs}}' => Str::kebab($models),
            '{{kebab}}' => $kebab,
            '{{Titles}}' => Str::headline($models),
            '{{Title}}' => Str::headline($model),
        ];

        $targets = [
            'migration' => database_path('migrations/'.now()->format('Y_m_d_His').'_create_'.$vars['{{table}}'].'_table.php'),
            'model' => app_path("Models/{$model}.php"),
            'factory' => database_path("factories/{$model}Factory.php"),
            'seeder' => database_path("seeders/{$model}PermissionSeeder.php"),
            'request' => app_path("Http/Requests/{$models}/Store{$model}Request.php"),
            'request-update' => app_path("Http/Requests/{$models}/Update{$model}Request.php"),
            'resource' => app_path("Http/Resources/{$models}/{$model}Resource.php"),
            'service' => app_path("Services/{$models}/{$model}Service.php"),
            'controller' => app_path("Http/Controllers/API/{$models}/{$model}Controller.php"),
            'test' => base_path("tests/Feature/{$models}/{$model}Test.php"),
        ];

        if (! $this->option('force') && $files->exists($targets['model'])) {
            $this->error("{$model} already exists (app/Models/{$model}.php). Use --force to overwrite.");

            return self::FAILURE;
        }

        foreach ($targets as $stub => $path) {
            $files->ensureDirectoryExists(dirname($path));
            $files->put($path, $this->render($files, $stub, $vars));
            $this->line('  <fg=green>created</> '.Str::after($path, base_path().'/'));
        }

        $this->insertAt($files, base_path('routes/api.php'), '// [make:module routes]', $this->render($files, 'routes', $vars));
        $this->insertAt($files, database_path('seeders/PermissionSeeder.php'), '// [make:module seeders]', "        {$model}PermissionSeeder::class,\n");
        $this->insertAt($files, app_path('Http/Controllers/API/Administration/AuditTrailController.php'), '// [make:module records]',
            "        \\App\\Models\\{$model}::class => '{$vars['{{Title}}']}',\n");
        // attachments + approvals on its view page
        $this->insertAt($files, app_path('Support/RecordTypes.php'), '// [make:module record-types]',
            "        '{$kebab}' => ['model' => \\App\\Models\\{$model}::class, 'label' => '{$vars['{{Title}}']}', 'link' => '/{$vars['{{kebabs}}']}/{id}', 'title' => 'name', 'approvals' => true],\n");

        $this->newLine();
        $this->info("{$vars['{{Titles}}']} module created. Next:");
        $this->line('  php artisan migrate && php artisan db:seed --class=PermissionSeeder');
        $this->line("  permissions: {$kebab}-list, {$kebab}-create, {$kebab}-edit, {$kebab}-delete");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $vars
     */
    private function render(Filesystem $files, string $stub, array $vars): string
    {
        return strtr($files->get(base_path("stubs/module/{$stub}.stub")), $vars);
    }

    // insert $code on the line before $marker (keeps the marker for the next module); once only
    private function insertAt(Filesystem $files, string $path, string $marker, string $code): void
    {
        $content = $files->get($path);
        if (! str_contains($content, $marker)) {
            throw new RuntimeException("Marker {$marker} not found in {$path}.");
        }
        if (str_contains($content, trim(strtok($code, "\n")))) {
            return;
        }
        $at = strrpos(substr($content, 0, strpos($content, $marker)), "\n") + 1;
        $files->put($path, substr($content, 0, $at).$code.substr($content, $at));
        $this->line('  <fg=yellow>updated</> '.Str::after($path, base_path().'/'));
    }
}
