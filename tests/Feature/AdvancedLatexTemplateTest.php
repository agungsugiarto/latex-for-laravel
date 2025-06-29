<?php

it('handles complex latex template with all blade features', function () {
    $view = view('latex.advanced-template', [
        'title' => 'Advanced LaTeX Document',
        'author' => 'John Doe',
        'date' => '2025-06-28',
        'sections' => [
            [
                'title' => 'Introduction',
                'content' => 'This is the introduction section.',
                'code' => 'echo "Hello World";',
            ],
            [
                'title' => 'Methodology',
                'content' => 'This explains our methodology.',
            ],
        ],
        'includeAppendix' => true,
        'appendixContent' => '<strong>Important notes</strong> and additional details.',
    ]);

    $rendered = $view->render();

    expect($rendered)->toContain('Advanced LaTeX Document')
        ->and($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('2025-06-28')
        ->and($rendered)->toContain('Introduction')
        ->and($rendered)->toContain('This is the introduction section.')
        ->and($rendered)->toContain('echo "Hello World";')
        ->and($rendered)->toContain('Methodology')
        ->and($rendered)->toContain('This explains our methodology.')
        ->and($rendered)->toContain('Additional Information')
        ->and($rendered)->toContain('<strong>Important notes</strong>');
});

it('creates valid latex that can be compiled directly', function () {
    // Test that the view can be loaded and rendered
    $view = view('latex.standalone-latex', []);
    $rendered = $view->render();

    // Check that the LaTeX structure is preserved
    expect($rendered)->toContain('\documentclass{article}')
        ->and($rendered)->toContain('\newcommand{\blade}[1]{}')
        ->and($rendered)->toContain('\begin{document}')
        ->and($rendered)->toContain('\end{document}')
        ->and($rendered)->toContain('E = mc^2')
        ->and($rendered)->toContain('My Document Title')
        ->and($rendered)->toContain('Author Name');
});
