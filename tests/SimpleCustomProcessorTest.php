<?php

it('can access latex compiler from service container', function () {
    $compiler = app('latex.compiler');

    expect($compiler)->toBeInstanceOf(\Agnula\LatexForLaravel\View\Compilers\LatexCompiler::class);
});

it('can add processors to compiler', function () {
    $compiler = app('latex.compiler');

    // This should not throw an exception
    $result = $compiler->addProcessor(function ($content, $next) {
        return $next($content);
    });

    expect($result)->toBe($compiler);
});

it('can add restorers to compiler', function () {
    $compiler = app('latex.compiler');

    // This should not throw an exception
    $result = $compiler->addRestorer(function ($content, $next) {
        return $next($content);
    });

    expect($result)->toBe($compiler);
});
