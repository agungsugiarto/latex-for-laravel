<?php

it('can render latex view with blade directives using real Laravel view', function () {
    // Setup view path to use our templates directory
    $this->app['view']->addLocation(__DIR__ . '/templates');

    // Test data
    $data = [
        'title' => 'My Test Document',
        'author' => 'John Doe',
        'date' => 'June 2025',
        'user' => (object) [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'score' => 95
        ],
        'desa' => (object) [
            'kecamatan' => 'Terara'
        ]
    ];

    // Render the view using the template file
    $rendered = view('simple-document', $data)->render();

    // Assertions - variables now render without extra braces
    expect($rendered)->toContain('My Test Document')
        ->and($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Alice Smith')
        ->and($rendered)->toContain('alice@example.com')
        ->and($rendered)->toContain('95')
        ->and($rendered)->toContain('This is literal content')
        ->and($rendered)->toContain('Terara')
        ->and($rendered)->toContain('\documentclass{article}')
        ->and($rendered)->toContain('\begin{document}')
        ->and($rendered)->toContain('\end{document}')
        ->and($rendered)->not->toContain('\blade{{')
        ->and($rendered)->not->toContain('\blade{!!');
});

it('handles the specific bug case correctly in real Laravel view', function () {
    // Setup view path to use our templates directory
    $this->app['view']->addLocation(__DIR__ . '/templates');

    $data = [
        'desa' => (object) [
            'kabupaten' => 'LOMBOK TIMUR',
            'kecamatan' => 'Terara'
        ],
        'hello' => 'World',
        'unescaped' => 'Content'
    ];

    $rendered = view('bug-test', $data)->render();

    // Should contain "Terara" without extra braces
    expect($rendered)->toContain('Terara')
        ->and($rendered)->toContain('\Large')
        ->and($rendered)->toContain('\textbf')
        ->and($rendered)->not->toContain('\blade{{');
});
