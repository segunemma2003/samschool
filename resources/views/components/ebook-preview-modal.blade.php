@props(['url', 'ext'])

@php
    $ext = strtolower($ext);
@endphp

@if(in_array($ext, ['pdf']))
    <iframe src="{{ $url }}" width="100%" height="500px"></iframe>

@elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
    <img src="{{ $url }}" alt="Preview" class="w-full h-auto max-h-[500px]" />

@elseif(in_array($ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx']))
    {{-- Google Docs Viewer fallback for Office files --}}
    <iframe src="https://docs.google.com/viewer?url={{ urlencode($url) }}&embedded=true"
        width="100%" height="500px" frameborder="0">
    </iframe>

@else
    <p class="text-gray-500">Preview not available for this file type.</p>
@endif
