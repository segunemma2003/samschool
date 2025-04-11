@php
    $url = Storage::disk('s3')->temporaryUrl($getState(), now()->addMinutes(5));
@endphp

<iframe src="{{ $url }}" width="100%" height="400px"></iframe>
