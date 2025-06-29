<?php

it('renders simple document template correctly', function () {
    $data = [
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

    $rendered = view('latex.simple-document', $data)->render();

    expect($rendered)
        ->toContain('My Test Document')
        ->toContain('John Doe')
        ->toContain('Alice Smith')
        ->toContain('alice@example.com')
        ->toContain('95')
        ->toContain('This is literal content')
        ->toContain('Terara')
        ->toContain('\documentclass{article}')
        ->toContain('\begin{document}')
        ->toContain('\end{document}')
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!');
});

it('handles bug test template correctly', function () {
    $data = [
        'desa' => (object) [
            'kabupaten' => 'LOMBOK TIMUR',
            'kecamatan' => 'TERARA',
        ],
        'hello' => 'World',
        'unescaped' => 'Content',
    ];

    $rendered = view('latex.bug-test', $data)->render();

    expect($rendered)
        // Test the main bug case
        ->toContain('PEMERINTAH KABUPATEN LOMBOK TIMUR')
        ->toContain('TERARA')
        ->toContain('World')
        ->toContain('Content')
        ->toContain('literal text')

        // Ensure LaTeX structure is preserved
        ->toContain('\documentclass{article}')
        ->toContain('\Large')
        ->toContain('\textbf')

        // Ensure no blade directives remain
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!')

        // Ensure variables don't have extra braces beyond LaTeX commands
        ->not->toContain('{LOMBOK TIMUR}'); // This would indicate double braces
});
