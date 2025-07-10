<?php
namespace App\Traits;

trait CachedResourceDiscovery
{
    private function getCachedResources(string $path, $panel, string $namespace): array
    {
        $cacheKey = "filament_resources_{$panel->getId()}_{$path}";

        return cache()->remember($cacheKey, 3600, function () use ($path, $namespace) {
            if (!is_dir($path)) {
                return [];
            }

            $resources = [];
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
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

    private function getCachedPages(string $path, $panel, string $namespace): array
    {
        $cacheKey = "filament_pages_{$panel->getId()}_{$path}";

        return cache()->remember($cacheKey, 3600, function () use ($path, $namespace) {
            if (!is_dir($path)) {
                return [];
            }

            $pages = [];
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $className = $this->getClassNameFromFile($file, $namespace);
                    if (class_exists($className) && is_subclass_of($className, \Filament\Pages\Page::class)) {
                        $pages[] = $className;
                    }
                }
            }

            return $pages;
        });
    }

    private function getClassNameFromFile(\SplFileInfo $file, string $namespace): string
    {
        // Get the relative path from the app directory
        $relativePath = str_replace(app_path(), '', $file->getPath());

        // Convert path separators to namespace separators
        $namespacePath = str_replace('/', '\\', trim($relativePath, '/'));

        // Build the full class name
        $className = 'App\\' . $namespacePath . '\\' . $file->getBasename('.php');

        return $className;
    }
}
