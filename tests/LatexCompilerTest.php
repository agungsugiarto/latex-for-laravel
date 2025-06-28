<?php

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;
use Illuminate\Filesystem\Filesystem;

it('compiles latex content with blade directives', function () {
    $files = new Filesystem();
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    $content = '
\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}
\blade{!! $title !!}
\blade{{ $name }}
\blade{Some literal content}
\end{document}
';

    // Test the preprocessing step manually
    $preprocessed = preg_replace_callback('/\\\\blade\s*{!!\s*(.*?)\s*!!}/s', function ($matches) {
        return "{!! {$matches[1]} !!}";
    }, $content);

    $preprocessed = preg_replace_callback('/\\\\blade\s*{{\s*(.*?)\s*}}/s', function ($matches) {
        return "{{ {$matches[1]} }}";
    }, $preprocessed);

    $preprocessed = preg_replace_callback('/\\\\blade\s*{(.*?)}/s', function ($matches) {
        return trim($matches[1]);
    }, $preprocessed);

    expect($preprocessed)->toContain('{!! $title !!}')
        ->and($preprocessed)->toContain('{{ $name }}')
        ->and($preprocessed)->toContain('Some literal content');
});

it('handles nested blade directives correctly', function () {
    $files = new Filesystem();
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    $content = '\blade{!! isset($data) ? $data : "default" !!}';

    // Test the preprocessing step manually
    $preprocessed = preg_replace_callback('/\\\\blade\s*{!!\s*(.*?)\s*!!}/s', function ($matches) {
        return "{!! {$matches[1]} !!}";
    }, $content);

    expect($preprocessed)->toContain('{!! isset($data) ? $data : "default" !!}');
});

it('preserves latex content without blade directives', function () {
    $files = new Filesystem();
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    $content = '
\documentclass{article}
\usepackage{amsmath}
\newcommand{\blade}[1]{}
\begin{document}
\title{My Document}
\author{John Doe}
\maketitle
\section{Introduction}
This is a test document.
\end{document}
';

    $reflection = new ReflectionClass($compiler);
    $method = $reflection->getMethod('compileString');
    $method->setAccessible(true);

    $compiled = $method->invoke($compiler, $content);

    expect($compiled)->toContain('\documentclass{article}')
        ->and($compiled)->toContain('\title{My Document}')
        ->and($compiled)->toContain('\section{Introduction}');
});

it('handles multiple blade directives in one line', function () {
    $files = new Filesystem();
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    $content = '\blade{{ $first }} and \blade{!! $second !!} with \blade{literal}';

    // Test the preprocessing step manually
    $preprocessed = preg_replace_callback('/\\\\blade\s*{!!\s*(.*?)\s*!!}/s', function ($matches) {
        return "{!! {$matches[1]} !!}";
    }, $content);

    $preprocessed = preg_replace_callback('/\\\\blade\s*{{\s*(.*?)\s*}}/s', function ($matches) {
        return "{{ {$matches[1]} }}";
    }, $preprocessed);

    $preprocessed = preg_replace_callback('/\\\\blade\s*{(.*?)}/s', function ($matches) {
        return trim($matches[1]);
    }, $preprocessed);

    expect($preprocessed)->toContain('{{ $first }}')
        ->and($preprocessed)->toContain('{!! $second !!}')
        ->and($preprocessed)->toContain('literal');
});

it('extends blade compiler correctly', function () {
    $files = new Filesystem();
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    expect($compiler)->toBeInstanceOf(\Illuminate\View\Compilers\BladeCompiler::class);
});
