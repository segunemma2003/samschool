<?php

namespace App\Filament\Hostel\Pages;

use App\Models\HostelAssignment;
use App\Models\HostelAttendance;
use App\Models\HostelBuilding;
use App\Models\HostelLeaveApplication;
use App\Models\HostelRoom;
use App\Models\SchoolClass;
use Filament\Pages\Page;

class HostelAnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.hostel.pages.hostel-analytics-dashboard';
    protected static ?string $navigationGroup = 'Hostel Management';
    protected static ?string $title = 'Hostel Analytics';

    public $startDate;
    public $endDate;
    public $buildingId;
    public $classId;

    public function mount()
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getBuildingsProperty()
    {
        return HostelBuilding::all();
    }

    public function getClassesProperty()
    {
        return SchoolClass::all();
    }

    public function getOccupancyDataProperty()
    {
        return HostelRoom::query()
            ->when($this->buildingId, fn($q) => $q->whereHas('floor', fn($q) => $q->where('hostel_building_id', $this->buildingId)))
            ->selectRaw('count(*) as total_rooms, sum(capacity) as total_capacity, sum(current_occupancy) as total_occupancy')
            ->first();
    }

    public function getClassDistributionDataProperty()
    {
        return HostelAssignment::query()
            ->whereNull('release_date')
            ->with('student.class')
            ->get()
            ->groupBy('student.class.name')
            ->map(fn($items) => $items->count());
    }

    public function getAttendanceTrendDataProperty()
    {
        return HostelAttendance::query()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->selectRaw('date, count(*) as total, sum(case when status = "present" then 1 else 0 end) as present')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getLeaveTrendDataProperty()
    {
        return HostelLeaveApplication::query()
            ->whereBetween('start_date', [$this->startDate, $this->endDate])
            ->selectRaw('date(start_date) as date, count(*) as total, sum(case when status = "approved" then 1 else 0 end) as approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
