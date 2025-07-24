<?php

namespace App\Filament\Teacher\Resources\ResultResource\Pages;

use App\Filament\Teacher\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    public function getTitle(): string
    {
        return 'Student Results Overview';
    }

    public function getDescription(): ?string
    {
        return 'A lively summary and detailed breakdown of all student marks for this term. Use filters and search to quickly find results.';
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create button, as results are generated from your query
        ];
    }

    // Add a summary widget above the table
    protected function getTableContent(): ?\Illuminate\View\View
    {
        // Fetch summary data from the table query
        $results = $this->getTableQuery()->get();
        $totalStudents = $results->count();
        $averageMark = $results->avg('mark_obtained');
        $highestMark = $results->max('mark_obtained');
        $lowestMark = $results->min('mark_obtained');

        return view('filament.teacher.resources.result.summary-widget', [
            'totalStudents' => $totalStudents,
            'averageMark' => $averageMark,
            'highestMark' => $highestMark,
            'lowestMark' => $lowestMark,
        ]);
    }

    protected static string $view = "filament.teacher.resources.result.view";
}
