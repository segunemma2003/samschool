<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Hero Section --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 dark:from-primary-800 dark:to-primary-950 p-6 shadow-lg">
            <div class="relative z-10">
                <h1 class="text-2xl font-bold tracking-tight text-white/80 sm:text-3xl">Student Library</h1>
                <p class="mt-2 text-white/80">Browse our collection, manage your loans, and request books</p>
            </div>
            <div class="absolute right-0 top-0 -translate-y-1/4 translate-x-1/3 opacity-20">
                <x-heroicon-o-book-open class="h-64 w-64 text-white" />
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 gap-2">
            <button
                wire:click="$set('activeTab', 'books')"
                class="px-4 py-3 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-t-lg
                    {{ $activeTab === 'books' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            >
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-book-open class="w-5 h-5" />
                    <span>Books Catalog</span>
                </div>
            </button>
            <button
                wire:click="$set('activeTab', 'current-loans')"
                class="px-4 py-3 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-t-lg
                    {{ $activeTab === 'current-loans' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            >
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-clock class="w-5 h-5" />
                    <span>Current Loans</span>
                    @if($this->currentLoans->count() > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-white text-primary-700">{{ $this->currentLoans->count() }}</span>
                    @endif
                </div>
            </button>
            <button
                wire:click="$set('activeTab', 'requests')"
                class="px-4 py-3 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-t-lg
                    {{ $activeTab === 'requests' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            >
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                    <span>My Requests</span>
                    @if($this->bookRequests->count() > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-white text-primary-700">{{ $this->bookRequests->count() }}</span>
                    @endif
                </div>
            </button>
        </div>

        {{-- Books Catalog Tab --}}
        <div class="animate-fade" x-show="$wire.activeTab === 'books'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="lg:flex lg:justify-between gap-4 mb-6">
                <div class="mb-4 lg:mb-0">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Library Catalog</h2>
                    <p class="text-gray-600 dark:text-gray-400">Browse available books and request to borrow them</p>
                </div>

                {{-- Filters --}}
                <div class="flex flex-wrap gap-2">
                    <div class="w-full sm:w-auto">
                        <select
                            wire:model.live="categoryFilter"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">All Categories</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select
                            wire:model.live="locationFilter"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">All Locations</option>
                            @foreach($this->locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-800">
                {{ $this->table }}
            </div>

            <div class="mt-4 bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-700 p-4 rounded-lg text-amber-800 dark:text-amber-300 flex items-start">
                <x-heroicon-o-light-bulb class="h-5 w-5 mt-0.5 mr-2 flex-shrink-0" />
                <p class="text-sm">
                    <strong>Tip:</strong> You can request to borrow books by clicking the "Borrow" button on available books. If a book is unavailable, you can still request it and you'll be notified when it becomes available.
                </p>
            </div>
        </div>

        {{-- Current Loans Tab --}}
        <div class="animate-fade" x-show="$wire.activeTab === 'current-loans'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Current Loans</h2>
                    <p class="text-gray-600 dark:text-gray-400">View and manage your currently borrowed books</p>
                </div>
            </div>

            @if($this->currentLoans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($this->currentLoans as $loan)
                        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full transform transition-all duration-200 hover:shadow-md">
                            <div class="relative">
                                <div class="h-28 bg-gradient-to-r from-blue-400 to-indigo-500 dark:from-blue-600 dark:to-indigo-800"></div>
                                <div class="absolute -bottom-8 left-4 h-16 w-12 shadow-md rounded overflow-hidden border-2 border-white dark:border-gray-700">
                                    @if($loan->book->cover_image)
                                        <img src="{{ Storage::disk('s3')->url($loan->book->cover_image) }}" class="h-full w-full object-cover" alt="Book cover">
                                    @else
                                        <div class="h-full w-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <x-heroicon-o-book-open class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                                        </div>
                                    @endif
                                </div>
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $loan->status === 'borrowed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $loan->status === 'overdue' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                        {{ $loan->status === 'lost' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    ">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-4 pt-10 flex-grow">
                                <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ $loan->book->title }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">By {{ $loan->book->author }}</p>

                                <div class="mt-4 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Borrowed:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $loan->loan_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Due:</span>
                                        <span class="text-sm font-medium {{ $loan->status === 'overdue' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-200' }}">
                                            {{ $loan->due_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @if($loan->status === 'overdue')
                                        <div class="text-sm text-red-600 dark:text-red-400">
                                            Overdue by {{ now()->diffInDays($loan->due_date) }} days
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 pt-0 mt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                                <button
                                    wire:click="showReturnModal({{ $loan->id }})"
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                                >
                                    <x-heroicon-o-arrow-uturn-left class="h-4 w-4 mr-1" />
                                    Return
                                </button>

                                @if($loan->status !== 'overdue')
                                    <button
                                        wire:click="requestExtension({{ $loan->id }})"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md text-primary-700 dark:text-primary-300 bg-primary-100 dark:bg-primary-900/50 hover:bg-primary-200 dark:hover:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                                        wire:confirm="Are you sure you want to extend this loan by one week?"
                                    >
                                        <x-heroicon-o-calendar class="h-4 w-4 mr-1" />
                                        Extend
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm border border-gray-200 dark:border-gray-700 text-center">
                    <div class="flex justify-center">
                        <x-heroicon-o-book-open class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No Books on Loan</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">You don't have any books on loan currently.</p>
                    <button
                        wire:click="$set('activeTab', 'books')"
                        class="mt-6 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                    >
                        <x-heroicon-o-book-open class="h-5 w-5 mr-1.5" />
                        Browse Books
                    </button>
                </div>
            @endif
        </div>

        {{-- Book Requests Tab --}}
        <div class="animate-fade" x-show="$wire.activeTab === 'requests'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Book Requests</h2>
                    <p class="text-gray-600 dark:text-gray-400">Track the status of your book requests</p>
                </div>
            </div>

            @if($this->bookRequests->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Book</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->bookRequests as $request)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($request->book->cover_image)
                                                    <img src="{{ Storage::disk('s3')->url($request->book->cover_image) }}" class="h-12 w-9 mr-3 rounded shadow-sm object-cover" alt="Book cover">
                                                @else
                                                    <div class="h-12 w-9 mr-3 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                        <x-heroicon-o-book-open class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $request->book->title }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->book->author }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $request->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                            ">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                            <div class="tooltip">
                                                <span class="max-w-xs truncate block">{{ $request->reason }}</span>
                                                <span class="tooltiptext">{{ $request->reason }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($request->status === 'pending')
                                                <button
                                                    wire:click="cancelRequest({{ $request->id }})"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 flex items-center"
                                                    wire:confirm="Are you sure you want to cancel this request?"
                                                >
                                                    <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                                                    Cancel
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm border border-gray-200 dark:border-gray-700 text-center">
                    <div class="flex justify-center">
                        <x-heroicon-o-paper-airplane class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No Book Requests</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">You haven't requested any books yet.</p>
                    <button
                        wire:click="$set('activeTab', 'books')"
                        class="mt-6 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                    >
                        <x-heroicon-o-book-open class="h-5 w-5 mr-1.5" />
                        Browse Books
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Return Book Modal --}}
    <x-filament::modal id="return-book-modal" :visible="$showReturnModal" width="md">
        <x-slot name="heading">
            Return Book
        </x-slot>

        <div class="space-y-6">
            @if($selectedLoan)
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0 h-20 w-14 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 shadow-sm">
                        @if($selectedLoan->book->cover_image)
                            <img src="{{ Storage::disk('s3')->url($selectedLoan->book->cover_image) }}" class="h-full w-full object-cover" alt="Book cover">
                        @else
                            <div class="h-full w-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <x-heroicon-o-book-open class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $selectedLoan->book->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">By {{ $selectedLoan->book->author }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Borrowed on {{ $selectedLoan->loan_date->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Book Condition</h4>
                    <div class="space-y-3">
                        <label class="flex items-center space-x-3">
                            <input
                                type="radio"
                                name="bookCondition"
                                value="1"
                                wire:model="bookConditionGood"
                                class="h-5 w-5 rounded-full border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-primary-600 dark:focus:ring-opacity-25"
                            >
                            <div>
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Good condition</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">The book is in the same condition as when borrowed</p>
                            </div>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input
                                type="radio"
                                name="bookCondition"
                                value="0"
                                wire:model="bookConditionGood"
                                class="h-5 w-5 rounded-full border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-primary-600 dark:focus:ring-opacity-25"
                            >
                            <div>
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Damaged or not in good condition</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">The book has sustained damage or wear beyond normal use</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="returnNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                    <textarea
                        id="returnNotes"
                        wire:model="returnNotes"
                        rows="3"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Add any notes about the book condition or return process..."
                    ></textarea>
                </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-end space-x-2">
                <x-filament::button color="gray" wire:click="$set('showReturnModal', false)">
                    Cancel
                </x-filament::button>
                <x-filament::button
                    wire:click="confirmReturn"
                    class="inline-flex items-center justify-center"
                >
                    <x-heroicon-o-check class="mr-1 h-4 w-4" />
                    Confirm Return
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Add custom tooltip styles --}}
    <style>
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 300px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 8px 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 0;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            line-height: 1.25rem;
            pointer-events: none;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        .animate-fade {
            animation: fadeEffect 0.5s;
        }

        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    </style>
</x-filament-panels::page>
