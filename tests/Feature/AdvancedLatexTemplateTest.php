<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

it('handles complex latex template with all blade features', function () {
    $viewPath = resource_path('views/advanced-template.blade.tex');
    $viewDir = dirname($viewPath);

    if (! File::exists($viewDir)) {
        File::makeDirectory($viewDir, 0755, true);
    }

    $latexContent = '
\documentclass{article}
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

\blade{@if(isset($section[\'code\']))}
\begin{verbatim}
\blade{!! $section[\'code\'] !!}
\end{verbatim}
\blade{@endif}

\blade{@endforeach}

\blade{@if($includeAppendix)}
\appendix
\section{Additional Information}
\blade{!! $appendixContent !!}
\blade{@endif}

\end{document}
';

    File::put($viewPath, $latexContent);

    $view = View::make('advanced-template', [
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

    // Clean up
    File::delete($viewPath);
});

it('creates valid latex that can be compiled directly', function () {
    $viewPath = resource_path('views/standalone-latex.blade.tex');
    $viewDir = dirname($viewPath);

    if (! File::exists($viewDir)) {
        File::makeDirectory($viewDir, 0755, true);
    }

    // This LaTeX template should be valid even without processing Blade directives
    $latexContent = '
\documentclass{article}
\usepackage[utf8]{inputenc}
\newcommand{\blade}[1]{}

\begin{document}

\title{\blade{My Document Title}}
\author{\blade{Author Name}}
\maketitle

\section{\blade{Introduction}}
\blade{This document demonstrates how Blade directives are hidden from LaTeX.}

\blade{The \newcommand{\blade}[1]{} definition makes all \blade{...} commands invisible to LaTeX.}

\section{Mathematical Formulas}
Here is a formula that LaTeX will render:
\[
E = mc^2
\]

\blade{And here is some dynamic content that would be processed by Blade.}

\end{document}
';

    File::put($viewPath, $latexContent);

    // Test that the view can be loaded and rendered
    $view = View::make('standalone-latex', []);
    $rendered = $view->render();

    // Check that the LaTeX structure is preserved
    expect($rendered)->toContain('\documentclass{article}')
        ->and($rendered)->toContain('\newcommand{\blade}[1]{}')
        ->and($rendered)->toContain('\begin{document}')
        ->and($rendered)->toContain('\end{document}')
        ->and($rendered)->toContain('E = mc^2')
        ->and($rendered)->toContain('My Document Title')
        ->and($rendered)->toContain('Author Name');

    // Clean up
    File::delete($viewPath);
});
