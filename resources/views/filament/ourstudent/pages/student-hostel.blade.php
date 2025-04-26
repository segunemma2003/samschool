<x-filament-panels::page>
    <x-filament-panels::header
        heading="Student Hostel Dashboard"
    />

    @if(!$application)
        <x-filament::section>
            <div class="flex flex-col space-y-4">
                <h2 class="text-lg font-medium">Hostel Application</h2>
                <p>You don't have an active hostel application for the current term.</p>
                <x-filament::button
                    wire:click="applyForHostel"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-not-allowed"
                >
                    <span wire:loading.remove>Apply for Hostel</span>
                    <span wire:loading>
                        <x-filament::loading-indicator class="w-4 h-4" />
                        Processing...
                    </span>
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Application Status Card -->
            <x-filament::card>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Hostel Application Status</h3>
                    <div class="space-y-2">
                        <p>
                            <span class="font-medium">Status:</span>
                            <x-filament::badge :color="match($application->status) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'gray',
                            }">
                                {{ ucfirst($application->status) }}
                            </x-filament::badge>
                        </p>
                        @if($assignment)
                            <p><span class="font-medium">Room:</span> {{ $assignment->hostelRoom->name }}</p>
                            <p><span class="font-medium">Bed:</span> {{ $assignment->bed->name }}</p>
                        @endif
                    </div>
                </div>
            </x-filament::card>

            <!-- Leave Applications Section -->
            <x-filament::card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Leave Applications</h3>
                        <x-filament::modal>
                            <x-slot name="trigger">
                                <x-filament::button size="sm">
                                    Request Leave
                                </x-filament::button>
                            </x-slot>

                            <div class="space-y-4">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <x-filament::input
                                            type="date"
                                            wire:model="start_date"
                                            required
                                            class="mt-1 block w-full"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                                        <x-filament::input
                                            type="date"
                                            wire:model="end_date"
                                            required
                                            class="mt-1 block w-full"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                                        <x-filament::input
                                            type="textarea"
                                            wire:model="reason"
                                            required
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                </div>

                                <x-filament::button
                                    type="button"
                                    class="mt-4 w-full"
                                    wire:click="requestLeave"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-70 cursor-not-allowed"
                                >
                                    <span wire:loading.remove>Submit Leave Request</span>
                                    <span wire:loading>
                                        <x-filament::loading-indicator class="w-4 h-4" />
                                        Processing...
                                    </span>
                                </x-filament::button>
                            </div>
                        </x-filament::modal>
                    </div>

                    @if($leaveApplications->isEmpty())
                        <p class="text-gray-500">No active leave applications found.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($leaveApplications as $leave)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">
                                                {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-600">{{ Str::limit($leave->reason, 60) }}</p>
                                        </div>
                                        <x-filament::badge :color="match($leave->status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            default => 'gray',
                                        }">
                                            {{ ucfirst($leave->status) }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-filament::card>

            <!-- Attendance Section -->
            <x-filament::card>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Recent Attendance</h3>
                    @if($attendances->isEmpty())
                        <p class="text-gray-500">No attendance records found.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($attendances as $attendance)
                                <div class="flex items-center justify-between border-b pb-2">
                                    <div>
                                        <p class="font-medium">{{ $attendance->date->format('D, M d') }}</p>
                                        <p class="text-sm text-gray-600">{{ $attendance->status }}</p>
                                    </div>
                                    <x-filament::badge :color="$attendance->status === 'present' ? 'success' : 'danger'">
                                        {{ ucfirst($attendance->status) }}
                                    </x-filament::badge>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-filament::card>
        </div>
    @endif
</x-filament-panels::page>
