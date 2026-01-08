@extends('layouts.authenticated')

@section('title', '- Role & Permissions')
@section('header-title', 'Kelola Kewenangan')

@section('content')
<div class="container mx-auto px-4 max-w-full">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelola Kewenangan Role</h1>
            <p class="text-gray-600 mt-2">Centang permission untuk setiap role. Satu halaman untuk mengelola semua akses.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.role-permissions.update') }}" id="permissions-form">
        @csrf

        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Table Header -->
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 border-r-2 border-gray-300" style="min-width: 300px;">
                            Permission / Halaman
                        </th>
                        @foreach($roles as $role)
                            @php
                                $badgeClass = match($role->name) {
                                    'Admin' => 'bg-purple-500',
                                    'User' => 'bg-blue-500',
                                    'Operator-Vidcon' => 'bg-green-500',
                                    'Operator-Sandi' => 'bg-yellow-500',
                                    'User-Individual' => 'bg-teal-500',
                                    'User-OPD' => 'bg-indigo-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp
                            <th class="px-4 py-4 text-center border-r border-gray-200" style="min-width: 120px;">
                                <div class="inline-block px-3 py-1 rounded text-white text-xs font-semibold {{ $badgeClass }} mb-1">
                                    {{ $role->display_name }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $role->users->count() }} user
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($allPermissions as $group => $groupPermissions)
                        <!-- Group Header Row -->
                        <tr class="bg-gray-50" x-data="{ open: true }">
                            <td colspan="{{ $roles->count() + 1 }}" class="px-0 py-0">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="w-full px-6 py-3 flex items-center hover:bg-gray-100 transition text-left"
                                >
                                    <svg class="w-5 h-5 mr-2 transition-transform" :class="{ 'rotate-90': open }" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <h3 class="font-semibold text-gray-800">{{ $group ?: 'Lainnya' }}</h3>
                                    <span class="ml-2 text-xs text-gray-500">({{ $groupPermissions->count() }} permission)</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Permission Rows in Group -->
                        @foreach($groupPermissions as $permission)
                            <tr x-show="open" x-collapse class="hover:bg-gray-50 transition">
                                <!-- Permission Name -->
                                <td class="px-6 py-3 border-r-2 border-gray-200">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-700">{{ $permission->display_name }}</span>
                                        <span class="text-xs text-gray-400 font-mono mt-1">{{ $permission->name }}</span>
                                    </div>
                                </td>

                                <!-- Role Checkboxes (Horizontal) -->
                                @foreach($roles as $role)
                                    <td class="px-4 py-3 text-center border-r border-gray-100">
                                        <input
                                            type="checkbox"
                                            name="permissions[{{ $role->id }}][]"
                                            value="{{ $permission->id }}"
                                            {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                            class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Save Button (Sticky Bottom) -->
        <div class="sticky bottom-0 bg-white border-t-2 border-gray-300 shadow-lg mt-6 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span class="font-semibold">Total:</span> {{ $allPermissions->flatten()->count() }} permissions × {{ $roles->count() }} roles
                </div>
                <button
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-md transition"
                >
                    💾 Simpan Semua Kewenangan
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Alpine.js for collapse functionality --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
