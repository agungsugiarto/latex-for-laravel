<?php

it('can render government letter template', function () {
    // Use test data that matches the new template structure
    $data = [
        'desa' => (object) [
            'kabupaten' => 'LOMBOK TIMUR',
            'kecamatan' => 'TERARA',
            'sebutan' => 'DESA',
            'nama' => 'TANJUNG',
            'alamat' => 'Jl. Raya Tanjung No. 1, Kode Pos 83671',
        ],
        'surat' => (object) [
            'nomor' => '470/145/Ds.Tj/2024',
        ],
        'penduduk' => (object) [
            'nama' => 'AHMAD YUSUF',
            'nik' => '5203010101950001',
            'alamat' => 'Dusun Tanjung Barat RT/RW 001/002',
            'alamat_sebelumnya' => 'Alamat Lama',
            'paspor' => 'A1234567',
            'tgl_paspor' => '31 Desember 2025',
            'sex' => 'LAKI-LAKI',
            'tempat_lahir' => 'LOMBOK TIMUR',
            'tanggal_lahir' => '01 Januari 1995',
            'akta_lahir' => 'AL001',
            'gol_darah' => 'B',
            'agama' => 'ISLAM',
            'status_kawin' => 'KAWIN',
            'akta_nikah' => 'AN001',
            'tgl_nikah' => '15 Agustus 2020',
            'akta_cerai' => null,
            'tgl_cerai' => null,
            'status_hubungan' => 'KEPALA KELUARGA',
            'cacat' => 'TIDAK ADA',
            'pendidikan' => 'SMA/SEDERAJAT',
            'pekerjaan' => 'PETANI',
            'keluarga' => (object) [
                'kepala' => (object) [
                    'nama' => 'AHMAD YUSUF',
                ],
                'no_kk' => '5203010101950001',
            ],
            'ibu' => (object) [
                'nama' => 'SITI AISYAH',
                'nik' => '5203010101960002',
            ],
            'ayah' => (object) [
                'nama' => 'MUHAMMAD ALI',
                'nik' => '5203010101960001',
            ],
        ],
        'tanggal_surat' => '25 Juni 2025',
        'penandatangan' => (object) [
            'nama' => 'H. LALU AHMAD',
            'jabatan' => 'KEPALA DESA',
            'nip' => '196508151990031007',
        ],
    ];

    $rendered = view('latex.government-letter', $data)->render();

    expect($rendered)
        ->toContain('PEMERINTAH KABUPATEN LOMBOK TIMUR')
        ->toContain('KECAMATAN TERARA')
        ->toContain('DESA TANJUNG')
        ->toContain('SURAT KETERANGAN BIODATA PENDUDUK')
        ->toContain('470/145/Ds.Tj/2024')
        ->toContain('AHMAD YUSUF')
        ->toContain('5203010101950001')
        ->toContain('LAKI-LAKI')
        ->toContain('LOMBOK TIMUR, 01 Januari 1995')
        ->toContain('ISLAM')
        ->toContain('PETANI')
        ->toContain('SITI AISYAH')
        ->toContain('MUHAMMAD ALI')
        ->toContain('H. LALU AHMAD')
        ->toContain('KEPALA DESA')
        ->toContain('25 Juni 2025')
        ->toContain('\documentclass[10pt]{article}')
        ->toContain('\usepackage{tabularx}')
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!');
});

it('renders real world government letter template correctly', function () {
    // Sample data that would come from a government database
    $data = [
        'desa' => (object) [
            'kabupaten' => 'LOMBOK TIMUR',
            'kecamatan' => 'TERARA',
            'sebutan' => 'DESA',
            'nama' => 'TANJUNG',
            'alamat' => 'Jl. Raya Tanjung No. 1, Kode Pos 83671',
        ],
        'surat' => (object) [
            'nomor' => '470/145/Ds.Tj/2024',
        ],
        'penduduk' => (object) [
            'nama' => 'AHMAD YUSUF',
            'nik' => '5203010101950001',
            'alamat' => 'Dusun Tanjung Barat RT/RW 001/002',
            'alamat_sebelumnya' => null,
            'paspor' => null,
            'tgl_paspor' => null,
            'sex' => 'LAKI-LAKI',
            'tempat_lahir' => 'LOMBOK TIMUR',
            'tanggal_lahir' => '01 Januari 1995',
            'akta_lahir' => null,
            'gol_darah' => 'B',
            'agama' => 'ISLAM',
            'status_kawin' => 'KAWIN',
            'akta_nikah' => null,
            'tgl_nikah' => null,
            'akta_cerai' => null,
            'tgl_cerai' => null,
            'status_hubungan' => 'KEPALA KELUARGA',
            'cacat' => 'TIDAK ADA',
            'pendidikan' => 'SMA/SEDERAJAT',
            'pekerjaan' => 'PETANI',
            'keluarga' => (object) [
                'kepala' => (object) [
                    'nama' => 'AHMAD YUSUF',
                ],
                'no_kk' => '5203010101950001',
            ],
            'ibu' => (object) [
                'nama' => 'SITI AISYAH',
                'nik' => '5203010101960002',
            ],
            'ayah' => (object) [
                'nama' => 'MUHAMMAD ALI',
                'nik' => '5203010101960001',
            ],
        ],
        'tanggal_surat' => '25 Juni 2025',
        'penandatangan' => (object) [
            'nama' => 'H. LALU AHMAD',
            'jabatan' => 'KEPALA DESA',
            'nip' => '196508151990031007',
        ],
    ];

    $rendered = view('latex.government-letter', $data)->render();

    expect($rendered)
        ->toContain('PEMERINTAH KABUPATEN LOMBOK TIMUR')
        ->toContain('KECAMATAN TERARA')
        ->toContain('DESA TANJUNG')
        ->toContain('SURAT KETERANGAN BIODATA PENDUDUK')
        ->toContain('AHMAD YUSUF')
        ->toContain('5203010101950001')
        ->toContain('Dusun Tanjung Barat')
        ->toContain('H. LALU AHMAD')
        ->toContain('KEPALA DESA')
        ->toContain('25 Juni 2025')
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!');
});
