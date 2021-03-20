<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Commands\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait CanManipulateFiles {

    protected function checkForCollision($paths): bool
    {
        foreach ($paths as $path) {
            if ($this->fileExists($path)) {
                $this->error("$path already exists, aborting.");

                return true;
            }
        }

        return false;
    }

    protected function fileExists($path): bool
    {
        $filesystem = new Filesystem();

        return $filesystem->exists($path);
    }

    protected function writeFile($path, $contents)
    {
        $filesystem = new Filesystem();

        $filesystem->ensureDirectoryExists(
            (string) Str::of($path)
                ->beforeLast('/'),
        );

        $filesystem->put($path, $contents);
    }

    protected function copyStubToApp($stub, $targetPath, $replacements = [])
    {
        $filesystem = new Filesystem();

        $stubPath = __DIR__ . "/../stubs/{$stub}.stub";

        $stub = Str::of($filesystem->get($stubPath));

        foreach ($replacements as $key => $replacement) {
            $stub = $stub->replace("{{ {$key} }}", $replacement);
        }

        $this->writeFile($targetPath, (string) $stub);
    }
}
