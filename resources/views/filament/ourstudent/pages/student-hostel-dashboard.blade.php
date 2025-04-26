<x-filament-panels::page>
    <x-filament-panels::header
        :heading="__('Student Hostel Dashboard')"
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
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Application Status Card -->
            <x-filament::card>
                <div class="space-y-2">
                    <h3 class="text-lg font-medium">Hostel Application Status</h3>
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
            </x-filament::card>

            <!-- Leave Applications Card -->
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
                                        <x-filament::input type="date" wire:model="start_date" required class="mt-1 block w-full" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                                        <x-filament::input type="date" wire:model="end_date" required class="mt-1 block w-full" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                                        <x-filament::input type="textarea" wire:model="reason" required class="mt-1 block w-full" />
                                    </div>
                                </div>

                                <x-filament::button
                                    type="button"
                                    class="mt-4"
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
                        <p>No leave applications found.</p>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($leaveApplications as $leave)
                                <x-filament::card>
                                    <div class="space-y-1">
                                        <p class="font-medium">{{ $leave->start_date->format('M d, Y') }} to {{ $leave->end_date->format('M d, Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ Str::limit($leave->reason, 50) }}</p>
                                        <x-filament::badge :color="match($leave->status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        }">
                                            {{ ucfirst($leave->status) }}
                                        </x-filament::badge>
                                    </div>
                                </x-filament::card>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-filament::card>

            <!-- Maintenance Request Card -->
            <x-filament::card>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Report Maintenance Issue</h3>
                    <div class="space-y-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Issue Type</label>
                                <x-filament::input type="select" wire:model="issue_type" required class="mt-1 block w-full">
                                    <option value="">Select issue type</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="furniture">Furniture</option>
                                    <option value="other">Other</option>
                                </x-filament::input>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <x-filament::input type="textarea" wire:model="description" required class="mt-1 block w-full" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Priority</label>
                                <x-filament::input type="select" wire:model="priority" required class="mt-1 block w-full">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </x-filament::input>
                            </div>
                        </div>

                        <x-filament::button
                            type="button"
                            class="mt-4"
                            wire:click="reportMaintenanceIssue"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-not-allowed"
                        >
                            <span wire:loading.remove>Submit Maintenance Request</span>
                            <span wire:loading>
                                <x-filament::loading-indicator class="w-4 h-4" />
                                Processing...
                            </span>
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::card>
        </div>
    @endif
</x-filament-panels::page>
