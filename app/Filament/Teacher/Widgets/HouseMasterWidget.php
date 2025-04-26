<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\HostelBuilding;
use App\Models\HostelRoom;
use App\Models\ParentVisitRequest;
use App\Models\Teacher;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class HouseMasterWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Requests',$this->getPendingRequests())
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Current Occupancy',$this->getCurrentOccupancy())
                ->label('Current Occupancy')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Total Capacity', $this->getTotalCapacity())
                ->label('Total Capacity')
                ->icon('heroicon-o-home')
                ->color('primary'),
        ];


    }


    public function getBuilding()
    {
        $user = User::whereId(Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return HostelBuilding::where('house_master_id', $teacher->id)
            ->withCount(['floors', 'rooms'])
            ->first();
    }

    public function getPendingRequests()
    {
        return ParentVisitRequest::where('hostel_building_id', $this->getBuilding()?->id)
            ->where('status', 'pending')
            ->count();
    }

    public function getCurrentOccupancy()
    {
        return HostelRoom::whereHas('floor', function($query) {
                $query->where('hostel_building_id', $this->getBuilding()?->id);
            })
            ->sum('current_occupancy');
    }

    public function getTotalCapacity()
    {
        return HostelRoom::whereHas('floor', function($query) {
                $query->where('hostel_building_id', $this->getBuilding()?->id);
            })
            ->sum('capacity');
    }
}
