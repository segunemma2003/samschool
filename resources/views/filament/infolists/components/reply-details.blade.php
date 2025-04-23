{{-- resources/views/filament/complaints/reply-details.blade.php --}}
<div class="space-y-4">
    <div class="flex items-center gap-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100">
            <x-heroicon-o-user-circle class="w-8 h-8 text-gray-400" />
        </div>
        <div>
            <h3 class="font-bold text-lg">
                {{ $reply->is_admin ? 'Admin' : $reply->user->name }}
            </h3>
            <p class="text-sm text-gray-500">
                {{ $reply->created_at->format('M j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="prose max-w-none">
        {!! $reply->message !!}
    </div>
</div>
