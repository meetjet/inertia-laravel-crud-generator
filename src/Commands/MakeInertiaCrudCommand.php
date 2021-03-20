<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Wpseed\InertiaLaravelCrudGenerator\Commands\Concerns\CanManipulateFiles;

class MakeInertiaCrudCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:inertia:crud {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Inertia CRUD skeleton';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace(string $name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace;
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass(string $name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param string $model
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Execute the console command.
     */
    public function handle():void
    {
        $model = (string) Str::of($this->argument('name'))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');
        $modelClass = (string) Str::of($model)->afterLast('\\');
        $modelNamespace = Str::of($model)->contains('\\') ?
            (string) Str::of($model)->beforeLast('\\') :
            '';
        $pluralModelClass = (string) Str::of($modelClass)->pluralStudly();

        $controller = "{$model}Controller";
        $controllerClass = "{$modelClass}Controller";

        $baseControllerPath = app_path(
            (string) Str::of($controller)
                ->prepend('Http\\InertiaControllers\\')
                ->replace('\\', '/'),
        );
        $controllerPath = "{$baseControllerPath}.php";

        $baseInertiaPagePath = resource_path(
            (string) Str::of($model)->prepend('js/Pages/'),
        );
        $indexInertiaPagePath = "{$baseInertiaPagePath}/List.vue";
        $createInertiaPagePath = "{$baseInertiaPagePath}/Create.vue";
        $editInertiaPagePath = "{$baseInertiaPagePath}/Edit.vue";

        if ($this->checkForCollision([
            $controllerPath,
            $indexInertiaPagePath,
            $createInertiaPagePath,
            $editInertiaPagePath,
        ])) {
            return;
        }

        $this->copyStubToApp('Controller', $controllerPath, [
            'namespace' => 'App\\Filament\\Resources' . ($resourceNamespace !== '' ? "\\{$resourceNamespace}" : ''),
            'controller' => $controller,
            'controllerClass' => $controllerClass,
        ]);

        $this->copyStubToApp('Resource', $controllerPath, [
            'indexInertiaPage' => $indexResourcePageClass,
            'createInertiaPage' => $createResourcePageClass,
            'editInertiaPage' => $editResourcePageClass,
        ]);

        $this->info("Successfully created {$model} Inertia CRUD!");
    }
}
