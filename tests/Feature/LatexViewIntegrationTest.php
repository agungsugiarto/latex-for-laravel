<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

it('can create and compile latex views', function () {
    // Create a temporary view file
    $viewPath = resource_path('views/test.blade.tex');
    $viewDir = dirname($viewPath);

    if (!File::exists($viewDir)) {
        File::makeDirectory($viewDir, 0755, true);
    }

    $latexContent = '
\documentclass{article}
\usepackage[utf8]{inputenc}
\newcommand{\blade}[1]{}
\begin{document}
\title{\blade{{ $title }}}
\author{\blade{{ $author }}}
\maketitle

\section{Introduction}
\blade{!! $content !!}

\end{document}
';

    File::put($viewPath, $latexContent);

    // Create the view
    $view = View::make('test', [
        'title' => 'Test Document',
        'author' => 'John Doe',
        'content' => 'This is a test content.'
    ]);

    expect($view)->toBeInstanceOf(\Illuminate\View\View::class);

    // Test that the view can be rendered
    $rendered = $view->render();

    expect($rendered)->toContain('Test Document')
        ->and($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('This is a test content.');

    // Clean up
    File::delete($viewPath);
});

it('processes blade directives correctly in latex views', function () {
    $viewPath = resource_path('views/blade-test.blade.tex');
    $viewDir = dirname($viewPath);

    if (!File::exists($viewDir)) {
        File::makeDirectory($viewDir, 0755, true);
    }

    $latexContent = '
\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}

Raw output: \blade{!! $raw !!}
Escaped output: \blade{{ $escaped }}
Literal text: \blade{This is literal}

\blade{@if($showSection)}
\section{\blade{{ $sectionTitle }}}
\blade{@endif}

\end{document}
';

    File::put($viewPath, $latexContent);

    $view = View::make('blade-test', [
        'raw' => '<strong>Bold Text</strong>',
        'escaped' => 'Safe & Sound',
        'showSection' => true,
        'sectionTitle' => 'Dynamic Section'
    ]);

    $rendered = $view->render();

    expect($rendered)->toContain('<strong>Bold Text</strong>')
        ->and($rendered)->toContain('Safe &amp; Sound')
        ->and($rendered)->toContain('This is literal')
        ->and($rendered)->toContain('Dynamic Section');

    // Clean up
    File::delete($viewPath);
});

it('can use view extension blade.tex', function () {
    $viewPath = resource_path('views/extension-test.blade.tex');
    $viewDir = dirname($viewPath);

    if (!File::exists($viewDir)) {
        File::makeDirectory($viewDir, 0755, true);
    }

    $latexContent = '
\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}
\title{Extension Test}
\maketitle
Hello World!
\end{document}
';

    File::put($viewPath, $latexContent);

    // Should be able to find the view with .blade.tex extension
    expect(View::exists('extension-test'))->toBeTrue();

    $view = View::make('extension-test');
    $rendered = $view->render();

    expect($rendered)->toContain('Extension Test')
        ->and($rendered)->toContain('Hello World!');

    // Clean up
    File::delete($viewPath);
});
