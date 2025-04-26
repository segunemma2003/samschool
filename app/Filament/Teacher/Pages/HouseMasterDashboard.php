<?php

namespace App\Filament\Teacher\Pages;

use App\Models\HostelBuilding;
use App\Models\HostelMaintenanceRequest;
use App\Models\ParentVisitRequest;
use App\Models\Teacher;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HouseMasterDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.teacher.pages.house-master-dashboard';

    protected static ?string $navigationGroup = 'Hostel';
    protected static ?string $title = 'House Master Dashboard';
    protected static ?string $navigationLabel = 'My Hostel';

    public $building;
    public $pendingRequests;
    public $recentVisits;
    public $maintenanceRequests;

    public function mount()
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        $this->building = HostelBuilding::whereHas('currentHouseMaster', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id)
                 ->where('is_current', true);
        })
        ->with(['floors.rooms.currentAssignments.student'])
        ->first();

        if (!$this->building) {
            abort(403, 'You are not currently assigned as a house master');
        }

        $this->pendingRequests = ParentVisitRequest::where('hostel_building_id', $this->building?->id)
            ->where('status', 'pending')
            ->count();

        $this->recentVisits = ParentVisitRequest::where('hostel_building_id', $this->building?->id)
            ->where('status', 'completed')
            ->orderBy('actual_visit_date', 'desc')
            ->limit(5)
            ->get();

        $this->maintenanceRequests = HostelMaintenanceRequest::whereHas('room.floor', function($query) {
                $query->where('hostel_building_id', $this->building?->id);
            })
            ->where('status', '!=', 'completed')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();
    }

    public function approveVisit($visitId)
    {
        $visit = ParentVisitRequest::find($visitId);
        $visit->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->mount();

        Notification::make()
            ->title('Visit approved successfully')
            ->success()
            ->send();
    }
}
