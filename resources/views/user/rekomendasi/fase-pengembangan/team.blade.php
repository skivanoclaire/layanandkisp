@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kelola Tim Pengembangan</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }}</p>
            </div>
            <a href="{{ route('user.rekomendasi.fase.index', $proposal->id) }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                Kembali
            </a>
        </div>

        <!-- Add Team Member Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Tambah Anggota Tim</h2>
            <form method="POST" action="{{ route('user.rekomendasi.fase.team.add', $proposal->id) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('nama')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="peran" class="block text-sm font-medium text-gray-700 mb-1">
                            Peran/Posisi <span class="text-red-500">*</span>
                        </label>
                        <select id="peran" name="peran" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Peran --</option>
                            <option value="Project Manager">Project Manager</option>
                            <option value="Business Analyst">Business Analyst</option>
                            <option value="System Analyst">System Analyst</option>
                            <option value="Frontend Developer">Frontend Developer</option>
                            <option value="Backend Developer">Backend Developer</option>
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Mobile Developer">Mobile Developer</option>
                            <option value="UI/UX Designer">UI/UX Designer</option>
                            <option value="Database Administrator">Database Administrator</option>
                            <option value="Quality Assurance">Quality Assurance</option>
                            <option value="DevOps Engineer">DevOps Engineer</option>
                            <option value="Technical Writer">Technical Writer</option>
                        </select>
                        @error('peran')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="kontak" class="block text-sm font-medium text-gray-700 mb-1">
                            Kontak (Email/HP)
                        </label>
                        <input type="text" id="kontak" name="kontak"
                            placeholder="email@example.com atau 08xxx"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('kontak')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Anggota
                    </button>
                </div>
            </form>
        </div>

        <!-- Team Members List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Anggota Tim ({{ $proposal->timPengembangan->count() }})</h2>

            @if($proposal->timPengembangan->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Peran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kontak
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($proposal->timPengembangan as $index => $member)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">{{ $member->nama }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ $member->peran }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $member->kontak ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" action="{{ route('user.rekomendasi.fase.team.delete', [$proposal->id, $member->id]) }}"
                                            onsubmit="return confirm('Hapus anggota tim {{ $member->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500">Belum ada anggota tim. Tambahkan anggota tim menggunakan form di atas.</p>
                </div>
            @endif
        </div>

        <!-- Team Roles Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <h3 class="font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Informasi Peran Tim
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-800">
                <div><strong>Project Manager:</strong> Mengkoordinasi seluruh proyek</div>
                <div><strong>Business Analyst:</strong> Analisis kebutuhan bisnis</div>
                <div><strong>System Analyst:</strong> Desain arsitektur sistem</div>
                <div><strong>Developer:</strong> Pengembangan aplikasi</div>
                <div><strong>UI/UX Designer:</strong> Desain antarmuka pengguna</div>
                <div><strong>QA:</strong> Pengujian dan quality control</div>
                <div><strong>DevOps:</strong> Deployment dan infrastruktur</div>
                <div><strong>Technical Writer:</strong> Dokumentasi teknis</div>
            </div>
        </div>
    </div>
</div>
@endsection
