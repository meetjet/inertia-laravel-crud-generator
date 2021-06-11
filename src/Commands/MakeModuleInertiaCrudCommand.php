<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Nwidart\Modules\Facades\Module;
use Wpseed\InertiaLaravelCrudGenerator\Commands\Concerns\CanManipulateFiles;

class MakeModuleInertiaCrudCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:inertia:crud {name} {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Inertia CRUD skeleton in Nwidart Module with given name';

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
        $modulePath = Module::getModulePath($this->argument('module'));
        $rootNamespace = 'WeconfModules\\' . Str::ucfirst($this->argument('module'));

        $model = (string) Str::of($this->argument('name'))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');
        $pluralModel = (string) Str::of($model)->pluralStudly();
        $modelClass = (string) Str::of($model)->afterLast('\\');
        $modelNamespace = Str::of($model)->contains('\\') ?
            (string) Str::of($model)->beforeLast('\\') :
            '';
        $pluralModelClass = (string) Str::of($modelClass)->pluralStudly();

        $controller = "{$model}Controller";
        $controllerClass = "{$modelClass}Controller";

        $baseControllerPath = $modulePath . (string) Str::of($controller)
                ->prepend('Http\\Controllers\\Central\\')
                ->replace('\\', '/');
        $controllerPath = "{$baseControllerPath}.php";

        $baseInertiaPagePath = $modulePath . (string) Str::of($pluralModel)->prepend('js/Pages/');

        $indexInertiaPagePath = "{$baseInertiaPagePath}/Index.vue";
        $showInertiaPagePath = "{$baseInertiaPagePath}/Show.vue";
        $createInertiaPagePath = "{$baseInertiaPagePath}/Create.vue";
        $editInertiaPagePath = "{$baseInertiaPagePath}/Edit.vue";

        if ($this->checkForCollision([
            $controllerPath,
            $indexInertiaPagePath,
            $showInertiaPagePath,
            $createInertiaPagePath,
            $editInertiaPagePath,
        ])) {
            return;
        }

        $this->copyStubToApp('InertiaController', $controllerPath, [
            'namespace' => $rootNamespace . '\\Http\\Controllers' . ($modelNamespace !== '' ? "\\{$modelNamespace}" : ''),
            'rootNamespace' => $rootNamespace,
            'model' => $model,
            'models' => $pluralModel,
            'controller' => $controller,
            'controllerClass' => $controllerClass,
            'entity' => Str::lower($model),
            'entities' => (string) Str::of(Str::lower($model))->plural(),
        ], 'module');

        $this->copyStubToAppVue('Index', $indexInertiaPagePath, [
            'model' => $model,
            'models' => $pluralModel,
            'entity' => Str::lower($model),
            'entities' => (string) Str::of(Str::lower($model))->plural(),
        ], 'module');

        $this->copyStubToAppVue('Show', $showInertiaPagePath, [
            'model' => $model,
            'models' => $pluralModel,
            'entity' => Str::lower($model),
            'entities' => (string) Str::of(Str::lower($model))->plural(),
        ], 'module');

        $this->copyStubToAppVue('Create', $createInertiaPagePath, [
            'model' => $model,
            'models' => $pluralModel,
            'entity' => Str::lower($model),
            'entities' => (string) Str::of(Str::lower($model))->plural(),
        ], 'module');

        $this->copyStubToAppVue('Edit', $editInertiaPagePath, [
            'model' => $model,
            'models' => $pluralModel,
            'entity' => Str::lower($model),
            'entities' => (string) Str::of(Str::lower($model))->plural(),
        ], 'module');

        $this->info("Inertia CRUD for model:{$model} and module:{$this->argument('module')} created successfully!");
    }
}
