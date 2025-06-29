<?php

// Set up and tear down for each test
beforeEach(function () {
    // Clean up any existing files from previous tests
    cleanupTestFiles();

    // Ensure directories exist
    if (!is_dir(__DIR__.'/templates')) {
        mkdir(__DIR__.'/templates', 0755, true);
    }
    if (!is_dir(__DIR__.'/templates/components')) {
        mkdir(__DIR__.'/templates/components', 0755, true);
    }

    // Add template location to view
    $this->app['view']->addLocation(__DIR__.'/templates');
});

afterEach(function () {
    // Clean up after each test
    cleanupTestFiles();
});

function cleanupTestFiles() {
    $componentsDir = __DIR__.'/templates/components';
    $templatesDir = __DIR__.'/templates';

    // Clean up components directory
    if (is_dir($componentsDir)) {
        $files = glob($componentsDir.'/*');
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
        rmdir($componentsDir);
    }

    // Clean up only test-specific template files (not permanent ones)
    if (is_dir($templatesDir)) {
        $testTemplateFiles = [
            $templatesDir.'/component-test.blade.tex',
            $templatesDir.'/dynamic-component-test.blade.tex',
            $templatesDir.'/nested-component-test.blade.tex'
        ];
        foreach($testTemplateFiles as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
    }
}

function createComponent(string $name, string $content): void {
    file_put_contents(__DIR__."/templates/components/{$name}.blade.tex", $content);
}

function createTemplate(string $name, string $content): void {
    file_put_contents(__DIR__."/templates/{$name}.blade.tex", $content);
}

function assertBasicLatexStructure(string $rendered): void {
    expect($rendered)
        ->toContain('\documentclass{article}')
        ->toContain('\begin{document}')
        ->toContain('\end{document}');
}

describe('LaTeX Blade Components', function () {

it('renders basic component with conditional subsections', function () {
    // Create component template using helper - use \blade{} syntax for LaTeX compiler
    createComponent('latex-section', '\section{\blade{{ $title }}}
\blade{{ $content }}

\blade{@if($includeSubsections)}
\blade{@foreach($subsections as $subsection)}
\subsection{\blade{{ $subsection }}}
\blade{@endforeach}
\blade{@endif}');

    // Create main template using helper - use \blade{} syntax for LaTeX compiler
    createTemplate('component-test', '\documentclass{article}
\begin{document}

\blade{@component(\'components.latex-section\', [
    \'title\' => \'Introduction\',
    \'content\' => $intro,
    \'includeSubsections\' => true,
    \'subsections\' => [\'Overview\', \'Goals\']
])}
\blade{@endcomponent}

\blade{@component(\'components.latex-section\', [
    \'title\' => \'Conclusion\',
    \'content\' => $conclusion,
    \'includeSubsections\' => false
])}
\blade{@endcomponent}

\end{document}');

    $testData = [
        'intro' => 'This is the introduction section content.',
        'conclusion' => 'This is the conclusion section content.'
    ];

    $rendered = view('component-test', $testData)->render();

    // Assert basic LaTeX structure
    assertBasicLatexStructure($rendered);

    // Assert section content
    expect($rendered)
        ->toContain('\section{Introduction}')
        ->toContain('This is the introduction section content.')
        ->toContain('\subsection{Overview}')
        ->toContain('\subsection{Goals}')
        ->toContain('\section{Conclusion}')
        ->toContain('This is the conclusion section content.');

    // Verify conditional rendering: only Introduction has subsections
    $subsectionCount = substr_count($rendered, '\subsection{');
    expect($subsectionCount)->toBe(2, 'Only Introduction section should have subsections');
});

it('handles dynamic data binding in components', function () {
    // Create component template using helper - use \blade{} syntax for LaTeX compiler
    createComponent('data-section', '\section{\blade{{ $title }}}

\blade{@foreach($items as $item)}
\item \blade{{ $item[\'name\'] }}: \blade{{ $item[\'value\'] }}
\blade{@endforeach}');

    // Create main template using helper - use \blade{} syntax for LaTeX compiler
    createTemplate('dynamic-component-test', '\documentclass{article}
\begin{document}

\blade{@component(\'components.data-section\', [
    \'title\' => \'Statistics\',
    \'items\' => $stats
])}
\blade{@endcomponent}

\end{document}');

    $testData = [
        'stats' => [
            ['name' => 'Total Users', 'value' => '1,234'],
            ['name' => 'Active Sessions', 'value' => '89'],
            ['name' => 'Revenue', 'value' => '$12,345']
        ]
    ];

    $rendered = view('dynamic-component-test', $testData)->render();

    // Assert document structure
    assertBasicLatexStructure($rendered);

    // Assert component content
    expect($rendered)->toContain('\section{Statistics}');

    // Verify all items are rendered correctly
    foreach ($testData['stats'] as $stat) {
        expect($rendered)->toContain($stat['name'] . ': ' . $stat['value']);
    }
});

it('supports nested component composition', function () {
    // Create parent component using helper - use \blade{} syntax for LaTeX compiler
    createComponent('document-section', '\section{\blade{{ $title }}}
\blade{{ $content }}

\blade{@if(isset($subsection))}
\blade{@component(\'components.sub-section\', [
    \'title\' => $subsection[\'title\'],
    \'content\' => $subsection[\'content\']
])}
\blade{@endcomponent}
\blade{@endif}');

    // Create child component using helper - use \blade{} syntax for LaTeX compiler
    createComponent('sub-section', '\subsection{\blade{{ $title }}}
\blade{{ $content }}');

    // Create main template using helper - use \blade{} syntax for LaTeX compiler
    createTemplate('nested-component-test', '\documentclass{article}
\begin{document}

\blade{@component(\'components.document-section\', [
    \'title\' => \'Main Section\',
    \'content\' => \'This is the main content.\',
    \'subsection\' => [
        \'title\' => \'Sub Section\',
        \'content\' => \'This is sub content.\'
    ]
])}
\blade{@endcomponent}

\end{document}');

    $rendered = view('nested-component-test')->render();

    // Assert document structure
    assertBasicLatexStructure($rendered);

    // Assert nested component content
    expect($rendered)
        ->toContain('\section{Main Section}')
        ->toContain('This is the main content.')
        ->toContain('\subsection{Sub Section}')
        ->toContain('This is sub content.');
});

});
