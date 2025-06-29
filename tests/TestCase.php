<?php

namespace Agnula\LatexForLaravel\Tests;

use Agnula\LatexForLaravel\LatexForLaravelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure view paths for testing
        $this->setupViewPaths();
    }

    protected function getPackageProviders($app)
    {
        return [
            LatexForLaravelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Set up application configuration for testing
        $app['config']->set('view.paths', [
            __DIR__ . '/../workbench/resources/views',
            __DIR__ . '/../resources/views',
        ]);
    }

    /**
     * Set up view paths for testing latex templates
     */
    protected function setupViewPaths(): void
    {
        $workbenchViewPath = __DIR__ . '/../workbench/resources/views';
        $resourcesViewPath = __DIR__ . '/../resources/views';

        // Add both workbench and package views
        if (is_dir($workbenchViewPath)) {
            $this->app['view']->addLocation($workbenchViewPath);
        }

        if (is_dir($resourcesViewPath)) {
            $this->app['view']->addLocation($resourcesViewPath);
        }
    }

    /**
     * Get test data for simple document tests
     */
    protected function getSimpleDocumentData(): array
    {
        return [
            'title' => 'My Test Document',
            'author' => 'John Doe',
            'date' => 'June 2025',
            'user' => (object) [
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'score' => 95,
            ],
            'desa' => (object) [
                'kecamatan' => 'Terara',
            ],
        ];
    }

    /**
     * Get test data for bug test cases
     */
    protected function getBugTestData(): array
    {
        return [
            'desa' => (object) [
                'kabupaten' => 'LOMBOK TIMUR',
                'kecamatan' => 'Terara',
            ],
            'hello' => 'World',
            'unescaped' => 'Content',
        ];
    }

    /**
     * Get test data for complex document tests
     */
    protected function getComplexDocumentData(): array
    {
        return [
            'title' => 'Complex Document',
            'author' => 'Dr. Smith',
            'date' => '2025-06-29',
            'sections' => [
                [
                    'title' => 'Introduction',
                    'content' => 'This is the introduction.',
                    'subsections' => [
                        [
                            'title' => 'Background',
                            'content' => '\textbf{Important background information}',
                        ],
                        [
                            'title' => 'Objectives',
                            'content' => '\textit{Research objectives}',
                        ],
                    ],
                ],
                [
                    'title' => 'Methodology',
                    'content' => 'This describes the methodology.',
                ],
            ],
            'includeReferences' => true,
            'bibliographyFile' => 'references.bib',
        ];
    }

    /**
     * Get test data for PHP code tests
     */
    protected function getPhpCodeTestData(): array
    {
        return [
            'title' => 'PHP Code Test',
            'data' => 'hello world',
            'items' => ['First', 'Second', 'Third'],
        ];
    }

    /**
     * Get test data for government letter tests
     */
    protected function getGovernmentLetterData(): array
    {
        return [
            'desa' => (object) [
                'kabupaten' => 'LOMBOK TIMUR',
                'kecamatan' => 'TERARA',
                'sebutan' => 'DESA',
                'nama' => 'TANJUNG',
                'alamat' => 'Jl. Raya Tanjung No. 1, Kode Pos 83671',
            ],
            'surat' => (object) [
                'nomor' => '470/TEST/Ds.Tj/2024',
            ],
            'penduduk' => (object) [
                'nama' => 'TEST USER',
                'nik' => '5203010101950001',
                'alamat' => 'Dusun Test RT/RW 001/002',
                'alamat_sebelumnya' => 'Alamat Test Lama',
                'paspor' => 'T1234567',
                'tgl_paspor' => '31 Desember 2025',
                'sex' => 'LAKI-LAKI',
                'tempat_lahir' => 'LOMBOK TIMUR',
                'tanggal_lahir' => '01 Januari 1995',
                'akta_lahir' => 'TL001',
                'gol_darah' => 'B',
                'agama' => 'ISLAM',
                'status_kawin' => 'KAWIN',
                'akta_nikah' => 'TN001',
                'tgl_nikah' => '15 Agustus 2020',
                'akta_cerai' => null,
                'tgl_cerai' => null,
                'status_hubungan' => 'KEPALA KELUARGA',
                'cacat' => 'TIDAK ADA',
                'pendidikan' => 'SMA/SEDERAJAT',
                'pekerjaan' => 'TESTER',
                'keluarga' => (object) [
                    'kepala' => (object) [
                        'nama' => 'TEST USER',
                    ],
                    'no_kk' => '5203010101950001',
                ],
                'ibu' => (object) [
                    'nama' => 'TEST IBU',
                    'nik' => '5203010101960002',
                ],
                'ayah' => (object) [
                    'nama' => 'TEST AYAH',
                    'nik' => '5203010101960001',
                ],
            ],
            'tanggal_surat' => '25 Juni 2025',
            'penandatangan' => (object) [
                'nama' => 'H. TEST OFFICIAL',
                'jabatan' => 'KEPALA DESA',
                'nip' => '196508151990031007',
            ],
        ];
    }
}
