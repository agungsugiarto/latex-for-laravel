<?php

it('handles tex-string compilation output', function () {
    // Create a simple template
    $templatePath = __DIR__.'/templates/output-test.blade.tex';

    $templateContent = '\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}

\title{\blade{{ $title }}}
\author{\blade{{ $author }}}

Simple document content.

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
        'title' => 'Test Document',
        'author' => 'Test Author',
    ];

    // Test tex-string output by checking that view renders correctly
    $rendered = view('output-test', $data)->render();

    expect($rendered)
        ->toContain('Test Document')
        ->toContain('Test Author')
        ->toContain('\documentclass{article}')
        ->toContain('Simple document content.')
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!');

    // Cleanup
    unlink($templatePath);
});

it('processes complex template structure correctly', function () {
    // Create template with all blade directive types
    $templatePath = __DIR__.'/templates/complex-output-test.blade.tex';

    $templateContent = '\documentclass{article}
\usepackage[utf8]{inputenc}
\usepackage{amsmath}
\usepackage{graphicx}
\newcommand{\blade}[1]{}

\begin{document}

\title{\blade{{ $title }}}
\author{\blade{{ $author }}}
\date{\blade{{ $date }}}
\maketitle

\tableofcontents

\blade{@foreach($sections as $section)}
\section{\blade{{ $section[\'title\'] }}}
\blade{{ $section[\'content\'] }}

\blade{@if(isset($section[\'subsections\']))}
\blade{@foreach($section[\'subsections\'] as $subsection)}
\subsection{\blade{{ $subsection[\'title\'] }}}
\blade{!! $subsection[\'content\'] !!}
\blade{@endforeach}
\blade{@endif}

\blade{@endforeach}

\blade{@if($includeReferences)}
\bibliography{\blade{{ $bibliographyFile }}}
\blade{@endif}

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
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

    $rendered = view('complex-output-test', $data)->render();

    expect($rendered)
        ->toContain('Complex Document')
        ->toContain('Dr. Smith')
        ->toContain('2025-06-29')
        ->toContain('\section{Introduction}')
        ->toContain('\section{Methodology}')
        ->toContain('\subsection{Background}')
        ->toContain('\subsection{Objectives}')
        ->toContain('This is the introduction.')
        ->toContain('This describes the methodology.')
        ->toContain('\textbf{Important background information}')
        ->toContain('\textit{Research objectives}')
        ->toContain('\bibliography{references.bib}')
        ->not->toContain('\blade{@foreach')
        ->not->toContain('\blade{@if');

    // Cleanup
    unlink($templatePath);
});

it('handles template with php code blocks', function () {
    // Create template with PHP code
    $templatePath = __DIR__.'/templates/php-code-test.blade.tex';

    $templateContent = '\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}

\title{\blade{{ $title }}}

\blade{@php
    $processedData = strtoupper($data);
    $formattedOutput = "Processed: " . $processedData;
@endphp}

Result: \blade{{ $formattedOutput }}

\blade{@php $counter = 0; @endphp}
\blade{@foreach($items as $item)}
\blade{@php $counter++; @endphp}
Item \blade{{ $counter }}: \blade{{ $item }}
\blade{@endforeach}

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
        'title' => 'PHP Code Test',
        'data' => 'hello world',
        'items' => ['First', 'Second', 'Third'],
    ];

    $rendered = view('php-code-test', $data)->render();

    expect($rendered)
        ->toContain('PHP Code Test')
        ->toContain('Processed: HELLO WORLD')
        ->toContain('Item 1: First')
        ->toContain('Item 2: Second')
        ->toContain('Item 3: Third')
        ->not->toContain('@php')
        ->not->toContain('@endphp');

    // Cleanup
    unlink($templatePath);
});

it('handles template with includes and yields', function () {
    // Create header partial
    $headerPath = __DIR__.'/templates/partials/latex-header.blade.tex';

    if (! is_dir(__DIR__.'/templates/partials')) {
        mkdir(__DIR__.'/templates/partials', 0755, true);
    }

    $headerContent = '\newcommand{\blade}[1]{}
\usepackage{amsmath}
\usepackage{graphicx}
% Header content from partial';

    file_put_contents($headerPath, $headerContent);

    // Create main template
    $templatePath = __DIR__.'/templates/include-test.blade.tex';

    $templateContent = '\documentclass{article}
\usepackage[utf8]{inputenc}
\newcommand{\blade}[1]{}
\blade{@include(\'partials.latex-header\')}

\begin{document}

\title{\blade{{ $title }}}

\blade{@yield(\'content\', \'Default content if no content section provided\')}

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
        'title' => 'Include Test Document',
    ];

    $rendered = view('include-test', $data)->render();

    expect($rendered)
        ->toContain('Include Test Document')
        ->toContain('\usepackage{amsmath}')
        ->toContain('\usepackage{graphicx}')
        ->toContain('Header content from partial')
        ->toContain('Default content if no content section provided')
        ->not->toContain('@include')
        ->not->toContain('@yield');

    // Cleanup
    unlink($headerPath);
    unlink($templatePath);
    rmdir(__DIR__.'/templates/partials');
});

it('preserves latex document structure integrity', function () {
    // Create comprehensive template
    $templatePath = __DIR__.'/templates/structure-test.blade.tex';

    $templateContent = '\documentclass[12pt,a4paper]{article}
\usepackage[utf8]{inputenc}
\usepackage[english]{babel}
\usepackage{amsmath}
\usepackage{amsfonts}
\usepackage{amssymb}
\usepackage{graphicx}
\usepackage[left=2cm,right=2cm,top=2cm,bottom=2cm]{geometry}
\newcommand{\blade}[1]{}

\begin{document}

\title{\blade{{ $documentTitle }}}
\author{\blade{{ $authorName }} \\ \blade{{ $institution }}}
\date{\blade{{ $submissionDate }}}
\maketitle

\begin{abstract}
\blade{{ $abstractText }}
\end{abstract}

\section{Introduction}
\label{sec:introduction}

\blade{@if($hasIntroduction)}
\blade{{ $introductionText }}
\blade{@else}
No introduction provided.
\blade{@endif}

\section{Mathematical Examples}

Inline math: $\blade{{ $inlineMath }}$

Display math:
\begin{equation}
\label{eq:main}
\blade{{ $displayMath }}
\end{equation}

Reference to equation \ref{eq:main} above.

\section{Conclusion}

\blade{@foreach($conclusions as $index => $conclusion)}
\blade{{ $index + 1 }}. \blade{{ $conclusion }}
\blade{@endforeach}

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
        'documentTitle' => 'Comprehensive LaTeX Document',
        'authorName' => 'John Smith',
        'institution' => 'University of Technology',
        'submissionDate' => 'June 2025',
        'abstractText' => 'This is a comprehensive test of LaTeX document structure.',
        'hasIntroduction' => true,
        'introductionText' => 'This document demonstrates various LaTeX features.',
        'inlineMath' => 'E = mc^2',
        'displayMath' => 'F = ma',
        'conclusions' => [
            'The structure is preserved correctly.',
            'All LaTeX commands remain valid.',
            'Blade directives are processed properly.',
        ],
    ];

    $rendered = view('structure-test', $data)->render();

    expect($rendered)
        // Document class and packages
        ->toContain('\documentclass[12pt,a4paper]{article}')
        ->toContain('\usepackage[utf8]{inputenc}')
        ->toContain('\usepackage{amsmath}')
        ->toContain('\usepackage{graphicx}')

        // Document content
        ->toContain('Comprehensive LaTeX Document')
        ->toContain('John Smith')
        ->toContain('University of Technology')
        ->toContain('June 2025')
        ->toContain('This is a comprehensive test')
        ->toContain('This document demonstrates')

        // Mathematical content
        ->toContain('$E = mc^2$')
        ->toContain('F = ma')
        ->toContain('\label{eq:main}')
        ->toContain('\ref{eq:main}')

        // List content
        ->toContain('1. The structure is preserved')
        ->toContain('2. All LaTeX commands remain valid')
        ->toContain('3. Blade directives are processed')

        // Structure elements
        ->toContain('\begin{abstract}')
        ->toContain('\end{abstract}')
        ->toContain('\section{Introduction}')
        ->toContain('\label{sec:introduction}')
        ->toContain('\begin{equation}')
        ->toContain('\end{equation}')

        // No blade artifacts
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!')
        ->not->toContain('@if')
        ->not->toContain('@foreach');

    // Cleanup
    unlink($templatePath);
});
