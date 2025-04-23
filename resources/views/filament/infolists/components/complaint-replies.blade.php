{{-- resources/views/filament/infolists/components/complaint-replies.blade.php --}}
<div class="space-y-4">
    @foreach($getState() as $reply)
        <div class="p-4 bg-white rounded-lg shadow
            {{ $reply->is_admin ? 'border-l-4 border-primary-500' : 'border-l-4 border-gray-300' }}">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-bold">
                        {{ $reply->is_admin ? 'Admin' : $reply->user->name }}
                    </p>
                    <p class="text-sm text-gray-500">{{ $reply->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="flex gap-2">
                    @if($reply->is_admin)
                        <span class="px-2 py-1 text-xs bg-primary-100 text-primary-800 rounded-full">
                            Admin
                        </span>
                    @endif
                    <x-filament::icon-button
                        icon="heroicon-o-eye"
                        color="gray"
                        size="sm"
                        tag="a"
                        href="{{ route('filament.resources.complaints.replies.view', ['record' => $reply->id]) }}"
                        tooltip="View details"
                    />
                </div>
            </div>
            <div class="mt-2 line-clamp-3 text-gray-600">
                {!! Str::limit(strip_tags($reply->message), 200) !!}
            </div>
        </div>
    @endforeach
</div>
