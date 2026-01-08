<?php

return [
    'google' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
        'spreadsheet_id' => env('GOOGLE_SHEETS_ASET_TIK_SPREADSHEET_ID'),

        'sheets' => [
            'ham' => [
                'name' => env('GOOGLE_SHEETS_HAM_SHEET_NAME', 'HAM-Register'),
                'gid' => env('GOOGLE_SHEETS_HAM_GID'),
                'range' => env('GOOGLE_SHEETS_HAM_RANGE', 'A:S'), // 19 kolom
                'header_row' => 1, // Baris 1 adalah header
            ],
            'sam' => [
                'name' => env('GOOGLE_SHEETS_SAM_SHEET_NAME', 'SAM-Register'),
                'gid' => env('GOOGLE_SHEETS_SAM_GID'),
                'range' => env('GOOGLE_SHEETS_SAM_RANGE', 'A:Z'), // 26 kolom
                'header_row' => 1,
            ],
            'kategori' => [
                'name' => env('GOOGLE_SHEETS_KATEGORI_SHEET_NAME', 'Kategori'),
                'range' => env('GOOGLE_SHEETS_KATEGORI_RANGE', 'B:C'),
                'header_row' => 1,
            ],
        ],
    ],

    'sync' => [
        'batch_size' => env('GOOGLE_SHEETS_SYNC_BATCH_SIZE', 50), // Turunkan ke 50 untuk stabilitas
        'timeout' => env('GOOGLE_SHEETS_SYNC_TIMEOUT', 300), // seconds
        'enable_auto_sync' => env('GOOGLE_SHEETS_AUTO_SYNC', false),
        'auto_sync_schedule' => 'monthly', // monthly, weekly, daily
    ],

    // Mapping kolom spreadsheet ke field database
    'column_mapping' => [
        'ham' => [
            'A' => 'no',
            'B' => 'nama_opd',
            'C' => 'nama_aset',
            'D' => 'kode_gab_barang',
            'E' => 'no_register',
            'F' => 'total',
            'G' => 'merk_type',
            'H' => 'tahun',
            'I' => 'nilai_perolehan',
            'J' => 'jenis_aset_tik',
            'K' => 'sumber_pendanaan',
            'L' => 'keadaan_barang',
            'M' => 'tanggal_perolehan',
            'N' => 'tanggal_penyerahan',
            'O' => 'asal_usul',
            'P' => 'status',
            'Q' => 'terotorisasi',
            'R' => 'aset_vital',
            'S' => 'keterangan',
        ],
        'sam' => [
            'A' => 'no',
            'B' => 'nama_opd',
            'C' => 'nama_aset',
            'D' => 'kode_barang',
            'E' => 'tahun',
            'F' => 'judul',
            'G' => 'harga',
            'H' => 'is_aktif',
            'I' => 'keterangan_software',
            'J' => 'jenis_perangkat_lunak',
            'K' => 'data_output',
            'L' => 'pengembangan',
            'M' => 'sewa',
            'N' => 'software_berjalan',
            'O' => 'fitur_sesuai',
            'P' => 'url',
            'Q' => 'integrasi',
            'R' => 'platform',
            'S' => 'database',
            'T' => 'script',
            'U' => 'framework',
            'V' => 'status',
            'W' => 'terotorisasi',
            'X' => 'aset_vital',
            'Y' => 'keterangan_utilisasi',
            'Z' => 'asal_usul',
        ],
    ],
];
