@extends('file-manager::share.layout')

@section('title', 'Download ' . $share->file->basename)

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg text-center">
        
        <!-- Icon -->
        <div class="mx-auto h-24 w-24 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
            <svg class="h-12 w-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>

        <!-- File Info -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2 break-all">
                {{ $share->file->basename }}
            </h2>
            <p class="text-gray-500 text-sm mb-4">
                {{ number_format($share->file->size / 1024 / 1024, 2) }} MB
            </p>
             <p class="text-xs text-gray-400">
                Shared by {{ $share->user->name ?? 'Unknown' }} • Expires {{ $share->expires_at ? $share->expires_at->diffForHumans() : 'Never' }}
            </p>
        </div>

        <!-- Actions -->
        <div class="mt-8 space-y-4">
            <a href="{{ route('share.download', $share->token) }}" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg transition-transform hover:scale-105">
                Download File
            </a>
            
             @if(str_starts_with($share->file->mime_type, 'image/') || str_starts_with($share->file->mime_type, 'video/') || $share->file->mime_type === 'application/pdf')
                <div class="mt-4">
                     <p class="text-sm text-gray-500 mb-2">Preview</p>
                     @if(str_starts_with($share->file->mime_type, 'image/'))
                        <img src="{{ route('share.preview', $share->token) }}" class="rounded-lg shadow-md max-h-64 mx-auto object-contain">
                     @elseif(str_starts_with($share->file->mime_type, 'video/'))
                        <video controls class="rounded-lg shadow-md w-full">
                            <source src="{{ route('share.preview', $share->token) }}" type="{{ $share->file->mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                     @elseif($share->file->mime_type === 'application/pdf')
                        <a href="{{ route('share.preview', $share->token) }}" target="_blank" class="text-indigo-600 hover:underline">View PDF in new tab</a>
                     @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
