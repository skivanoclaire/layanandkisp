<x-guest-layout>
    <div class="max-w-xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Konfirmasi Registrasi BSSN</h1>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-3">
            <p class="text-sm text-gray-600">No. Tiket: <span class="font-semibold text-gray-800">{{ $tteRegistration->ticket_no }}</span></p>
            <p class="text-sm text-gray-600">Nama: <span class="font-semibold text-gray-800">{{ $tteRegistration->nama }}</span></p>

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($tteRegistration->konfirmasi_bssn_at)
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm">
                    Registrasi BSSN sudah dikonfirmasi pada {{ $tteRegistration->konfirmasi_bssn_at->format('d M Y H:i') }}.
                </div>
            @else
                <p class="text-gray-700 text-sm">
                    Jika Anda sudah melakukan registrasi di BSSN untuk akun TTE, tekan tombol di bawah untuk mengonfirmasi kepada admin.
                </p>

                <form method="POST" action="{{ request()->fullUrl() }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-md">
                        Saya sudah melakukan registrasi BSSN
                    </button>
                </form>
            @endif
        </div>

        <p class="text-xs text-gray-500 mt-4">Helpdesk Bidang Persandian - DKISP Kaltara</p>
    </div>
</x-guest-layout>
