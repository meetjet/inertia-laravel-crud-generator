<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class InertiaLaravelCrudGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'inertia-laravel-crud-generator';
    }
}
