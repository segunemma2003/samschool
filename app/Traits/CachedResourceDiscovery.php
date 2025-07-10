<?php
namespace App\Traits;
// Create a base trait for cached resource discovery
trait CachedResourceDiscovery
{
    private function getCachedResources(string $path,$panel, string $namespace): array
    {
        $cacheKey = "filament_resources_{$panel->getId()}_{$path}";

        return cache()->remember($cacheKey, 3600, function () use ($path, $namespace) {
            if (!is_dir($path)) {
                return [];
            }

            $resources = [];
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $className = $this->getClassNameFromFile($file, $namespace);
                    if (class_exists($className) && is_subclass_of($className, \Filament\Resources\Resource::class)) {
                        $resources[] = $className;
                    }
                }
            }

            return $resources;
        });
    }

    private function getCachedPages(string $path, $panel,string $namespace): array
    {
        $cacheKey = "filament_pages_{$panel->getId()}_{$path}";

        return cache()->remember($cacheKey, 3600, function () use ($path, $namespace) {
            // Similar implementation for pages
            return $this->scanForPages($path, $namespace);
        });
    }

    private function getClassNameFromFile(\SplFileInfo $file, string $namespace): string
    {
        $relativePath = str_replace(app_path(), '', $file->getPath());
        $className = $namespace . '\\' . str_replace('/', '\\', trim($relativePath, '/')) . '\\' . $file->getBasename('.php');
        return $className;
    }
}
