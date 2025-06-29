<?php

beforeEach(function () {
    $compiler = app('latex.compiler');

    // Add mathematical notation processor
    $compiler->addProcessor(function ($content, $next) {
        // Convert \bladeformula{} to proper math environments
        $content = preg_replace_callback(
            '/\\\\bladeformula\s*{(.*?)}/s',
            function ($matches) {
                return "\\begin{align}\n".trim($matches[1])."\n\\end{align}";
            },
            $content
        );

        return $next($content);
    });

    // Add figure handling processor
    $compiler->addProcessor(function ($content, $next) {
        // Process dynamic figure inclusion
        $content = preg_replace(
            '/\\\\bladefigure\s*{([^}]+)}\s*{([^}]+)}\s*{([^}]+)}/',
            'FIG_MARKER[$1|$2|$3]',
            $content
        );

        return $next($content);
    });
});

it('processes bladeformula directives correctly', function () {
    // Create a simple test file
    $testPath = __DIR__.'/templates/formula-test.blade.tex';
    $content = '\bladeformula{E = mc^2}';
    file_put_contents($testPath, $content);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    // Render the template (this will use the full compile pipeline)
    $result = view('formula-test')->render();

    expect($result)->toContain("\\begin{align}\nE = mc^2\n\\end{align}");

    // Cleanup
    unlink($testPath);
});

it('processes bladefigure directives correctly', function () {
    // Create a simple test file
    $testPath = __DIR__.'/templates/figure-test.blade.tex';
    $content = '\bladefigure{images/chart.png}{Sales Chart}{fig:sales}';
    file_put_contents($testPath, $content);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    // Render the template (this will use the full compile pipeline)
    $result = view('figure-test')->render();

    expect($result)->toContain('FIG_MARKER[images/chart.png|Sales Chart|fig:sales]');

    // Cleanup
    unlink($testPath);
});

it('handles advanced template with multiple processors', function () {
    // Create template file
    $templatePath = __DIR__.'/templates/advanced-document.blade.tex';

    $templateContent = '\documentclass{article}
\usepackage{amsmath}
\usepackage{graphicx}

\begin{document}

\title{{{ $title }}}

% Using mathematical notation processor
\bladeformula{E = mc^2}

% Using figure processor (path, caption, label)
\bladefigure{images/chart.png}{Sales Chart}{fig:sales}

% Standard blade directives still work
@foreach($formulas as $formula)
Formula: {{ $formula->latex }}
@endforeach

\end{document}';

    file_put_contents($templatePath, $templateContent);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $data = [
        'title' => 'Advanced Scientific Document',
        'formulas' => [
            (object) ['latex' => 'F = ma'],
            (object) ['latex' => 'PV = nRT'],
        ],
    ];

    $rendered = view('advanced-document', $data)->render();

    expect($rendered)
        ->toContain('Advanced Scientific Document')
        ->toContain("\\begin{align}\nE = mc^2\n\\end{align}")
        ->toContain('FIG_MARKER[images/chart.png|Sales Chart|fig:sales]')
        ->toContain('F = ma')
        ->toContain('PV = nRT');

    // Cleanup
    unlink($templatePath);
});

it('preserves whitespace in formula processing', function () {
    // Create a simple test file
    $testPath = __DIR__.'/templates/whitespace-test.blade.tex';
    $content = '\bladeformula{  x^2 + y^2 = z^2  }';
    file_put_contents($testPath, $content);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $result = view('whitespace-test')->render();

    expect($result)->toContain("\\begin{align}\nx^2 + y^2 = z^2\n\\end{align}");

    // Cleanup
    unlink($testPath);
});

it('handles complex figure paths with spaces', function () {
    // Create a simple test file
    $testPath = __DIR__.'/templates/complex-figure-test.blade.tex';
    $content = '\bladefigure{path/to/my image.png}{Chart Title}{fig:complex}';
    file_put_contents($testPath, $content);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $result = view('complex-figure-test')->render();

    expect($result)->toContain('FIG_MARKER[path/to/my image.png|Chart Title|fig:complex]');

    // Cleanup
    unlink($testPath);
});

it('processes multiple formulas and figures in sequence', function () {
    // Create a simple test file
    $testPath = __DIR__.'/templates/sequence-test.blade.tex';
    $content = '
\bladeformula{E = mc^2}
\bladefigure{fig1.png}{Figure 1}{fig:1}
\bladeformula{F = ma}
\bladefigure{fig2.png}{Figure 2}{fig:2}
';
    file_put_contents($testPath, $content);

    // Setup view path
    $this->app['view']->addLocation(__DIR__.'/templates');

    $result = view('sequence-test')->render();

    expect($result)
        ->toContain("\\begin{align}\nE = mc^2\n\\end{align}")
        ->toContain('FIG_MARKER[fig1.png|Figure 1|fig:1]')
        ->toContain("\\begin{align}\nF = ma\n\\end{align}")
        ->toContain('FIG_MARKER[fig2.png|Figure 2|fig:2]');

    // Cleanup
    unlink($testPath);
});
