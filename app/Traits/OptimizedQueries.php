<?php
namespace App\Traits;

trait OptimizedQueries
{
    public function scopeWithMinimalData($query)
    {
        return $query->select($this->getMinimalColumns());
    }

    protected function getMinimalColumns(): array
    {
        return ['id', 'name', 'created_at']; // Override in models
    }

    public static function loadInBatches(array $ids, int $batchSize = 100)
    {
        return collect($ids)->chunk($batchSize)->flatMap(function ($chunk) {
            return static::whereIn('id', $chunk)->get();
        });
    }
}
