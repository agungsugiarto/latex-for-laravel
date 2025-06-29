<?php

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;

it('can add custom processors using service container binding', function () {
    $compiler = app('latex.compiler');

    expect($compiler)->toBeInstanceOf(LatexCompiler::class);

    // Add a custom processor for mathematical expressions
    $result = $compiler->addProcessor(function ($content, $next) {
        $content = preg_replace(
            '/\\\\math\s*{(.*?)}/s',
            '\\begin{equation}$1\\end{equation}',
            $content
        );

        return $next($content);
    });

    expect($result)->toBe($compiler);
});

it('can add custom restorers using service container binding', function () {
    $compiler = app('latex.compiler');

    // Add a custom processor that creates markers
    $compiler->addProcessor(function ($content, $next) {
        $content = preg_replace(
            '/\\\\customcmd\s*{(.*?)}/s',
            '###CUSTOM_START###$1###CUSTOM_END###',
            $content
        );

        return $next($content);
    });

    // Add a custom restorer for those markers
    $result = $compiler->addRestorer(function ($content, $next) {
        $content = preg_replace(
            '/###CUSTOM_START###(.*?)###CUSTOM_END###/',
            '<?php echo "Custom: $1"; ?>',
            $content
        );

        return $next($content);
    });

    expect($result)->toBe($compiler);
});

it('supports bibliography handling processor registration', function () {
    $compiler = app('latex.compiler');

    // Add processor for custom bibliography handling
    $result = $compiler->addProcessor(function ($content, $next) {
        // Process \bibref{} to \cite{}
        $content = str_replace('\bibref{', '\cite{', $content);

        return $next($content);
    });

    expect($result)->toBe($compiler);
});

it('handles multiple custom processors registration', function () {
    $compiler = app('latex.compiler');

    // Add first processor
    $result1 = $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('\first{', '\textbf{', $content);

        return $next($content);
    });

    // Add second processor
    $result2 = $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('\second{', '\textit{', $content);

        return $next($content);
    });

    expect($result1)->toBe($compiler);
    expect($result2)->toBe($compiler);
});

it('can chain processor and restorer registration', function () {
    $compiler = app('latex.compiler');

    // Test method chaining
    $result = $compiler
        ->addProcessor(function ($content, $next) {
            return $next($content);
        })
        ->addRestorer(function ($content, $next) {
            return $next($content);
        })
        ->addProcessor(function ($content, $next) {
            return $next($content);
        });

    expect($result)->toBe($compiler);
});
