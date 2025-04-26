<?php

namespace App\Filament\Teacher\Pages;

use App\Models\HostelAssignment;
use App\Models\HostelBuilding;
use App\Models\HostelRoom;
use App\Models\ParentVisitRequest;
use App\Models\Teacher;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HouseMasterReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.teacher.pages.house-master-report';

    protected static ?string $navigationGroup = 'Hostel';
    protected static ?string $title = 'Hostel Reports';
    protected static ?string $navigationLabel = 'Reports';

    public $startDate;
    public $endDate;
    public $reportType = 'occupancy';

    public function mount()
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getBuildingProperty()
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return HostelBuilding::where('house_master_id', $teacher->id)
            ->first();
    }

    public function getOccupancyDataProperty()
    {
        return HostelRoom::whereHas('floor', function($query) {
                $query->where('hostel_building_id', $this->building->id);
            })
            ->selectRaw('room_number, capacity, current_occupancy,
                        (current_occupancy/capacity)*100 as occupancy_rate')
            ->orderBy('occupancy_rate', 'desc')
            ->get();
    }

    public function getVisitTrendDataProperty()
    {
        return ParentVisitRequest::where('hostel_building_id', $this->building->id)
            ->whereBetween('proposed_visit_date', [$this->startDate, $this->endDate])
            ->selectRaw('date(proposed_visit_date) as date, count(*) as total,
                        sum(case when status = "approved" then 1 else 0 end) as approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getStudentDistributionProperty()
    {
        return HostelAssignment::whereHas('room.floor', function($query) {
                $query->where('hostel_building_id', $this->building->id);
            })
            ->whereNull('release_date')
            ->with('student.class')
            ->get()
            ->groupBy('student.class.name')
            ->map(fn($items) => $items->count());
    }
}
