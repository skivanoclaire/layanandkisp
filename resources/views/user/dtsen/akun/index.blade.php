@extends('layouts.authenticated')

@section('title', '- Akun Layanan DTSEN')
@section('header-title', 'Akun Layanan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Akun Layanan DTSEN</h1>
            <p class="text-gray-600 mt-1">Tahap 1 — pendaftaran akun perangkat daerah untuk berbagi pakai Data Tunggal Sosial Ekonomi Nasional.</p>
        </div>
        <a href="{{ route('user.dtsen.akun.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Daftarkan Akun</a>
    </div>

    @include('partials.dtsen.errors')

    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
        <p class="font-semibold">Ketentuan akun</p>
        <ul class="mt-1 list-disc list-inside space-y-0.5">
            <li>Surat permohonan akun wajib ditandatangani Kepala Perangkat Daerah (PDF, mendukung tanda tangan elektronik).</li>
            <li>Verifikasi oleh Admin DKISP ditargetkan selesai dalam 1 hari kerja sejak dokumen lengkap.</li>
            <li>Akun yang tidak digunakan selama {{ \App\Models\DtsenAccountRequest::IDLE_DAYS }} hari kalender dinonaktifkan otomatis dan perlu diaktifkan ulang.</li>
            <li>Surel pengguna dianjurkan memakai domain resmi pemerintah (<span class="font-mono">*.go.id</span>).</li>
        </ul>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Perangkat Daerah</th>
                    <th class="px-4 py-3 text-left">No. Surat</th>
                    <th class="px-4 py-3 text-center">Personel</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Keaktifan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->unitKerja->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nomor_surat }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->members_count }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->status === \App\Models\DtsenAccountRequest::STATUS_DISETUJUI)
                                @if ($item->is_active)
                                    <span class="text-green-700 font-semibold">Aktif</span>
                                    @if (($sisa = $item->idleDaysLeft()) !== null && $sisa <= \App\Models\DtsenAccountRequest::WARN_BEFORE_DAYS)
                                        <p class="text-xs text-orange-600">Nonaktif dalam {{ max($sisa, 0) }} hari bila tidak dipakai</p>
                                    @endif
                                @else
                                    <span class="text-red-600 font-semibold">Nonaktif</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('user.dtsen.akun.show', $item->id) }}" class="text-blue-600 hover:underline">Detail</a>
                            @if ($item->isEditableByOwner())
                                <a href="{{ route('user.dtsen.akun.edit', $item->id) }}" class="text-blue-600 hover:underline ml-2">Edit</a>
                            @endif
                            @if ($item->status === \App\Models\DtsenAccountRequest::STATUS_DRAFT)
                                <form action="{{ route('user.dtsen.akun.destroy', $item->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus draft permohonan akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada permohonan akun DTSEN.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
