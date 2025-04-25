<x-filament::page>
    <div class="space-y-6">
        @if(!$this->selectedMaterial)
            <!-- Browse View -->
            <div class="p-4 bg-white rounded-lg shadow">
                {{ $this->form }}
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->materials as $material)
                    <div class="overflow-hidden bg-white rounded-lg shadow">
                        <div class="p-4">
                            <div class="flex items-center space-x-4">
                                @if($material->cover_image)
                                    <img src="{{ Storage::disk('s3')->url($material->cover_image) }}"
                                         alt="{{ $material->title }}"
                                         class="w-16 h-16 object-cover rounded">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <x-heroicon-o-book-open class="w-8 h-8 text-gray-400" />
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $material->title }}</h3>
                                    <p class="text-sm text-gray-500">{{ $material->author }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($material->subjects as $subject)
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                {{ $subject->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-sm text-gray-600 line-clamp-3">
                                {{ $material->description }}
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 flex justify-between items-center">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <span>{{ $material->type->name }}</span>
                                <span>•</span>
                                <span>{{ $material->view_count }} views</span>
                            </div>

                            <button wire:click="viewMaterial({{ $material->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <span wire:loading.remove wire:target="viewMaterial({{ $material->id }})">Read</span>
                                <span wire:loading wire:target="viewMaterial({{ $material->id }})">
                                    <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-4 py-3 bg-white rounded-lg shadow">
                {{ $this->materials->links() }}
            </div>
        @else
            <!-- Reader View -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Toolbar -->
                <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                    <button wire:click="$set('selectedMaterial', null)"
                            class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Back to Library
                    </button>

                    <div class="flex items-center space-x-3">
                        <button wire:click="downloadMaterial({{ $this->selectedMaterial->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <span wire:loading.remove wire:target="downloadMaterial({{ $this->selectedMaterial->id }})">Download</span>
                            <span wire:loading wire:target="downloadMaterial({{ $this->selectedMaterial->id }})">
                                <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>

                        <button wire:click="toggleFavorite({{ $this->selectedMaterial->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <span wire:loading.remove wire:target="toggleFavorite({{ $this->selectedMaterial->id }})">
                                @if(auth()->user()->favorites()->where('material_id', $this->selectedMaterial->id)->exists())
                                    Remove Favorite
                                @else
                                    Add to Favorites
                                @endif
                            </span>
                            <span wire:loading wire:target="toggleFavorite({{ $this->selectedMaterial->id }})">
                                <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Document Viewer -->
                <div class="p-4">
                    @if($this->selectedMaterial->file_type === 'pdf')
                        <iframe src="{{ Storage::disk('s3')->temporaryUrl($this->selectedMaterial->file_path, now()->addMinutes(30)) }}#page={{ $this->currentPage }}"
                                class="w-full h-screen border rounded">
                        </iframe>
                    @else
                        <div class="flex items-center justify-center py-12 text-gray-500">
                            <div class="text-center">
                                <x-heroicon-o-document class="w-12 h-12 mx-auto" />
                                <p class="mt-2">Document preview not available for this file type.</p>
                                <button wire:click="downloadMaterial({{ $this->selectedMaterial->id }})"
                                        wire:loading.attr="disabled"
                                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <span wire:loading.remove wire:target="downloadMaterial({{ $this->selectedMaterial->id }})">Download to View</span>
                                    <span wire:loading wire:target="downloadMaterial({{ $this->selectedMaterial->id }})">
                                        <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Metadata -->
                <div class="px-4 py-3 border-t bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $this->selectedMaterial->title }}</h3>
                            <p class="text-sm text-gray-500">by {{ $this->selectedMaterial->author }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($this->selectedMaterial->subjects as $subject)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        {{ $subject->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="text-sm text-gray-500">
                                {{ $this->selectedMaterial->view_count }} views •
                                {{ $this->selectedMaterial->download_count }} downloads
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-medium text-gray-900">Reviews</h3>
                </div>

                <div class="p-4">
                    @if($this->selectedMaterial->reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($this->selectedMaterial->reviews as $review)
                                <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium">{{ $review->user->name }}</h4>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <x-heroicon-s-star class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                            @endfor
                                        </div>
                                    </div>
                                    @if($review->review)
                                        <p class="mt-2 text-sm text-gray-600">{{ $review->review }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No reviews yet.</p>
                    @endif

                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900">Add Your Review</h4>
                        <div class="mt-2 flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <button wire:click="$set('rating', {{ $i }})">
                                    <x-heroicon-o-star class="w-6 h-6 {{ $i <= $this->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                </button>
                            @endfor
                        </div>
                        <textarea wire:model="review"
                                  class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                  rows="3"
                                  placeholder="Write your review..."></textarea>
                        <button wire:click="submitReview"
                                wire:loading.attr="disabled"
                                class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <span wire:loading.remove wire:target="submitReview">Submit Review</span>
                            <span wire:loading wire:target="submitReview">
                                <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
