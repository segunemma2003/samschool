<x-filament::page>
    <x-filament::card>
        <form wire:submit.prevent="submitRequest">
            <div class="space-y-6">
                <h2 class="text-xl font-bold">Request Hostel Visit</h2>

                <!-- Student Selection -->
                <div class="space-y-1">
                    <label class="filament-forms-field-wrapper-label">
                        <span>Select Student</span>
                        <span class="text-danger-500">*</span>
                    </label>
                    <select
                        wire:model="student_id"
                        class="filament-forms-input rounded-lg border-gray-300 w-full"
                        required
                    >
                        <option value="">Select a student</option>
                        @forelse($this->students ?? [] as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->name }} ({{ $student->registration_number }})
                            </option>
                        @empty
                            <option value="" disabled>No students found</option>
                        @endforelse
                    </select>
                    @error('student_id')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hostel Building Selection -->
                <div class="space-y-1">
                    <label class="filament-forms-field-wrapper-label">
                        <span>Select Hostel Building</span>
                        <span class="text-danger-500">*</span>
                    </label>
                    <select
                        wire:model="building_id"
                        class="filament-forms-input rounded-lg border-gray-300 w-full"
                        required
                    >
                        <option value="">Select a hostel building</option>
                        @forelse($this->buildings ?? [] as $building)
                            <option value="{{ $building->id }}">
                                {{ $building->name }} ({{ $building->gender }})
                            </option>
                        @empty
                            <option value="" disabled>No buildings available</option>
                        @endforelse
                    </select>
                    @error('building_id')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Proposed Visit Date -->
                <div class="space-y-1">
                    <label class="filament-forms-field-wrapper-label">
                        <span>Proposed Visit Date & Time</span>
                        <span class="text-danger-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="proposed_visit_date"
                        class="filament-forms-input rounded-lg border-gray-300 w-full"
                        min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                        required
                    />
                    @error('proposed_visit_date')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purpose of Visit -->
                <div class="space-y-1">
                    <label class="filament-forms-field-wrapper-label">
                        <span>Purpose of Visit</span>
                        <span class="text-danger-500">*</span>
                    </label>
                    <textarea
                        wire:model="purpose"
                        class="filament-forms-input rounded-lg border-gray-300 w-full"
                        placeholder="Briefly explain the reason for your visit..."
                        rows="4"
                        required
                    ></textarea>
                    @error('purpose')
                        <p class="text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="filament-button filament-button-size-md inline-flex items-center justify-center py-2 px-4 text-sm gap-1 rounded-lg font-medium text-white shadow transition-colors bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 focus:ring-offset-white"
                    @if(!$this->students || count($this->students) === 0) disabled @endif
                >
                    Submit Request
                </button>

                @if(!$this->students || count($this->students) === 0)
                    <p class="text-sm text-danger-600">
                        You need to have at least one student registered to submit a visit request.
                    </p>
                @endif
            </div>
        </form>
    </x-filament::card>
</x-filament::page>
