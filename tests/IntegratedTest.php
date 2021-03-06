<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Tests;

use Illuminate\Support\Facades\Artisan;

class IntegratedTest extends TestCase
{
    public function test_new_crud_is_created()
    {
        Artisan::call('make:inertia:crud Page');

        $this->seeInConsoleOutput('Inertia CRUD for Page created successfully!');

        $this->assertTrue(is_dir(base_path('app/Http/InertiaControllers')));

        $this->assertTrue(is_dir(base_path('resources/js/Pages/Pages')));
    }
}
