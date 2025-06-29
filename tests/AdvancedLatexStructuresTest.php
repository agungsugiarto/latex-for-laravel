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
    // Render the template (this will use the full compile pipeline)
    $result = view('latex.formula-test')->render();

    expect($result)->toContain("\\begin{align}\nE = mc^2\n\\end{align}");
});

it('processes bladefigure directives correctly', function () {
    // Render the template (this will use the full compile pipeline)
    $result = view('latex.figure-test')->render();

    expect($result)->toContain('FIG_MARKER[images/chart.png|Sales Chart|fig:sales]');
});

it('handles advanced template with multiple processors', function () {
    $data = [
        'title' => 'Advanced Scientific Document',
        'formulas' => [
            (object) ['latex' => 'F = ma'],
            (object) ['latex' => 'PV = nRT'],
        ],
    ];

    $rendered = view('latex.advanced-document', $data)->render();

    expect($rendered)
        ->toContain('Advanced Scientific Document')
        ->toContain("\\begin{align}\nE = mc^2\n\\end{align}")
        ->toContain('FIG_MARKER[images/chart.png|Sales Chart|fig:sales]')
        ->toContain('F = ma')
        ->toContain('PV = nRT');
});

it('preserves whitespace in formula processing', function () {
    $result = view('latex.whitespace-test')->render();

    expect($result)->toContain("\\begin{align}\nx^2 + y^2 = z^2\n\\end{align}");
});

it('handles complex figure paths with spaces', function () {
    $result = view('latex.complex-figure-test')->render();

    expect($result)->toContain('FIG_MARKER[path/to/my image.png|Chart Title|fig:complex]');
});

it('processes multiple formulas and figures in sequence', function () {
    $result = view('latex.sequence-test')->render();

    expect($result)
        ->toContain("\\begin{align}\nE = mc^2\n\\end{align}")
        ->toContain('FIG_MARKER[fig1.png|Figure 1|fig:1]')
        ->toContain("\\begin{align}\nF = ma\n\\end{align}")
        ->toContain('FIG_MARKER[fig2.png|Figure 2|fig:2]');
});
