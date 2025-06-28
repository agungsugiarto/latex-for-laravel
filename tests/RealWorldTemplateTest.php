<?php

it('renders real world government letter template correctly', function () {
    // Setup view path to use our templates directory
    $this->app['view']->addLocation(__DIR__.'/templates');

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
            'tgl_paspor' => '-',
            'sex' => 'LAKI-LAKI',
            'tempat_lahir' => 'LOMBOK TIMUR',
            'tanggal_lahir' => '01 Januari 1995',
            'akta_lahir' => null,
            'gol_darah' => 'B',
            'agama' => 'ISLAM',
            'status_kawin' => 'KAWIN',
            'akta_nikah' => '123/NK/2020',
            'tgl_nikah' => '15 Februari 2020',
            'akta_cerai' => null,
            'tgl_cerai' => '-',
            'status_hubungan' => 'KEPALA KELUARGA',
            'cacat' => 'TIDAK ADA',
            'pendidikan' => 'SMA/SEDERAJAT',
            'pekerjaan' => 'PETANI',
            'keluarga' => (object) [
                'no_kk' => '5203010101950001',
                'kepala' => (object) [
                    'nama' => 'AHMAD YUSUF',
                ],
            ],
            'ibu' => (object) [
                'nama' => 'SITI AMINAH',
                'nik' => '5203014507650002',
            ],
            'ayah' => (object) [
                'nama' => 'MUHAMMAD ALI',
                'nik' => '5203011201600001',
            ],
        ],
        'tanggal_surat' => '28 Juni 2025',
        'penandatangan' => (object) [
            'nama' => 'H. ABDUL RAHMAN, S.Pd.',
            'jabatan' => 'KEPALA DESA TANJUNG',
            'nip' => '19751201 200604 1 003',
        ],
    ];

    // Render the view using the template file
    $rendered = view('government-letter', $data)->render();

    // Test that all variables are rendered correctly without braces
    expect($rendered)
        // Header information
        ->toContain('PEMERINTAH KABUPATEN LOMBOK TIMUR')
        ->toContain('KECAMATAN TERARA')
        ->toContain('DESA TANJUNG')
        ->toContain('Jl. Raya Tanjung No. 1, Kode Pos 83671')

        // Document number
        ->toContain('Nomor: 470/145/Ds.Tj/2024')

        // Family data
        ->toContain('AHMAD YUSUF')
        ->toContain('5203010101950001')

        // Personal data
        ->toContain('LAKI-LAKI')
        ->toContain('LOMBOK TIMUR, 01 Januari 1995')
        ->toContain('ISLAM')
        ->toContain('KAWIN')
        ->toContain('KEPALA KELUARGA')
        ->toContain('SMA/SEDERAJAT')
        ->toContain('PETANI')

        // Parent data
        ->toContain('SITI AMINAH')
        ->toContain('MUHAMMAD ALI')
        ->toContain('5203014507650002')
        ->toContain('5203011201600001')

        // Signature block
        ->toContain('TANJUNG, 28 Juni 2025')
        ->toContain('KEPALA DESA TANJUNG')
        ->toContain('H. ABDUL RAHMAN, S.Pd.')
        ->toContain('19751201 200604 1 003')

        // Ensure no blade directives remain
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!')

        // Ensure LaTeX structure is preserved
        ->toContain('\documentclass[10pt]{article}')
        ->toContain('\begin{document}')
        ->toContain('\end{document}')
        ->toContain('\section{Data Keluarga}')
        ->toContain('\section{Data Individu}')
        ->toContain('\section{Data Orang Tua}')
        ->toContain('\begin{tabularx}')
        ->toContain('\end{tabularx}')
        ->toContain('\textbf{')
        ->toContain('\Large')

        // Test complex nested variables work correctly
        ->toContain('Dusun Tanjung Barat RT/RW 001/002, DESA TANJUNG, KEC. TERARA, KAB. LOMBOK TIMUR')

        // Test null coalescing operators work
        ->toContain('& : & - \\') // For null values that should show as "-" in table format

        // Verify that variables don't have extra braces (beyond LaTeX formatting)
        ->not->toContain('{LOMBOK TIMUR}')
        ->not->toContain('{TERARA}')
        ->not->toContain('{DESA}');

    // Test specific patterns that were problematic before
    expect($rendered)
        ->toContain('PEMERINTAH KABUPATEN LOMBOK TIMUR') // Should not have {LOMBOK TIMUR}
        ->toContain('KECAMATAN TERARA') // Should not have {TERARA}
        ->toContain('DESA TANJUNG') // Should not have {DESA} or {TANJUNG}
        ->toContain('AHMAD YUSUF') // Names in tables should render cleanly
        ->toContain('NIP: 19751201 200604 1 003'); // Should not have {19751201 200604 1 003}

    // Count variables in the template file to verify comprehensive coverage
    $templateContent = file_get_contents(__DIR__.'/templates/government-letter.blade.tex');
    $escapedVarCount = substr_count($templateContent, '\blade{{');
    $unescapedVarCount = substr_count($templateContent, '\blade{!!');

    // Verify comprehensive coverage
    expect($escapedVarCount)->toBe(37);
    expect($unescapedVarCount)->toBe(5);
});
