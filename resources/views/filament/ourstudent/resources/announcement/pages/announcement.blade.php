<x-filament-panels::page>
    <div class="max-w-4xl p-6 mx-auto space-x-3 lg:p-8">
        <!-- Blog Post Title -->
        <h1 class="mb-4 text-3xl font-bold text-gray-800 dark:text-gray-100 lg:text-5xl lg:leading-tight">
            {{ $record->title }}
        </h1>

        <!-- Blog Post Subtitle -->
        <h2 class="mb-6 text-xl font-semibold text-gray-700 dark:text-gray-200 lg:text-3xl">
            {{ $record->sub }}
        </h2>
        <br/>
        @if(!is_null($record->file))
        <!-- Blog Post Image (optional) -->
        <img src="https://res.cloudinary.com/iamdevmaniac/image/upload/{{ $record->file }}" alt="{{ $record->file }}" class="w-full h-auto mb-8 transition-transform duration-300 rounded-lg shadow-lg hover:scale-105">
        @endif

        @if(!is_null($record->link))
        <!-- External Link (if available) -->
        <div class="mb-6">
            <a href="{{ $record->link }}" class="inline-block px-4 py-2 text-lg font-semibold text-white transition-colors duration-200 bg-indigo-600 rounded-md shadow-lg hover:bg-indigo-500">
                Click to view details
            </a>
        </div>
        @endif
        <br/>
        <!-- Blog Post Content -->
        <div class="mb-6 prose prose-lg text-justify text-gray-800 dark:text-gray-200 lg:prose-xl max-w-none">
            {!! str($record->text)->sanitizeHtml() !!}
        </div>
<br/>
        <!-- Post Meta Information -->
        <div class="text-gray-600 dark:text-gray-400">
            <p>Published at: <span class="font-medium">{{ $record->created_at->diffForHumans() }}</span></p>
            <p>Published by: <span class="font-medium">{{ $record->owner->name }}</span></p>
        </div>
    </div>
</x-filament-panels::page>
