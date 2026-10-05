@if (session('success'))
    <div class="mb-4 p-4 rounded-lg border bg-green-50 border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="mb-4 p-4 rounded-lg border bg-yellow-50 border-yellow-200 text-yellow-800 text-sm">{{ session('warning') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
@endif
