<?php

namespace Wpseed\InertiaLaravelCrudGenerator\Commands\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

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

        $stubPath = __DIR__ . "/stubs/{$stub}.stub";

        try {
            $stub = Str::of($filesystem->get($stubPath));
        } catch (FileNotFoundException $e) {
        }

        foreach ($replacements as $key => $replacement) {
            $stub = $stub->replace("{{ {$key} }}", $replacement);
        }

        $stub = (string) $stub;

        $this->writeFile($targetPath, $stub);
    }
}
