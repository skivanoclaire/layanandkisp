@extends('layouts.authenticated')

@section('title', '- Edit Perubahan/Perpanjangan SPLP')
@section('header-title', 'Edit Perubahan / Perpanjangan (SPLP)')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Permohonan — {{ $item->ticket_no }}</h1>
        <p class="text-gray-600 mt-1">Status:
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
        </p>
    </div>
    <form action="{{ route('user.splp.change.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('user.splp.change._form')
    </form>
</div>
@endsection
