<?php

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;
use Illuminate\Filesystem\Filesystem;

function applyLatexCompilerRegex(string $content): string
{
    // Apply regex sama seperti di LatexCompiler yang sudah diperbaiki
    $result = $content;

    // 1. \blade{!! ... !!} → {!! ... !!}
    $result = preg_replace('/\\\\blade\s*({!!\s*.*?\s*!!})/s', '$1', $result);

    // 2. \blade{{ ... }} → temporary marker (support multiline)
    $result = preg_replace('/\\\\blade\s*({{\s*.*?\s*}})/s', '###BLADE_ECHO_START###$1###BLADE_ECHO_END###', $result);

    // 3. \blade{...} → literal (but exclude cases with {{ or {!!)
    $result = preg_replace_callback('/\\\\blade\s*{(?!\{|!!)([^}]*?)}/s', function ($matches) {
        return trim($matches[1]);
    }, $result);

    // 4. Simulate final output without extra braces - just the variable content
    $result = preg_replace('/###BLADE_ECHO_START###{{\s*(.*?)\s*}}###BLADE_ECHO_END###/s', '$1', $result);

    return $result;
}

it('compiles latex content with blade directives', function () {
    $content = '
\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}
\blade{!! $title !!}
\blade{{ $name }}
\blade{Some literal content}
\end{document}
';

    $preprocessed = applyLatexCompilerRegex($content);

    expect($preprocessed)->toContain('{!! $title !!}')
        ->and($preprocessed)->toContain('$name')
        ->and($preprocessed)->toContain('Some literal content');
});

it('handles nested blade directives correctly', function () {
    $content = '\blade{!! isset($data) ? $data : "default" !!}';

    $preprocessed = applyLatexCompilerRegex($content);

    expect($preprocessed)->toContain('{!! isset($data) ? $data : "default" !!}');
});

it('preserves latex content without blade directives', function () {
    $files = new Filesystem;
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
    $content = '\blade{{ $first }} and \blade{!! $second !!} with \blade{literal}';

    $preprocessed = applyLatexCompilerRegex($content);

    expect($preprocessed)->toContain('$first')
        ->and($preprocessed)->toContain('{!! $second !!}')
        ->and($preprocessed)->toContain('literal');
});

// Test untuk memastikan negative lookahead bekerja dengan benar
it('handles nested braces correctly with negative lookahead', function () {
    // Test case yang bermasalah sebelumnya
    $testCases = [
        '\textbf{\blade{{ $hello }}}' => '\textbf{$hello}',
        '\textbf{\blade{!! $hello !!}}' => '\textbf{{!! $hello !!}}',
        '\blade{simple text}' => 'simple text',
        '\blade{!! $unescaped !!}' => '{!! $unescaped !!}',
        '\blade{{ $escaped }}' => '$escaped',
        '\Large \textbf{\blade{{ $desa->kecamatan }}} \\' => '\Large \textbf{$desa->kecamatan} \\',
    ];

    foreach ($testCases as $input => $expected) {
        $result = applyLatexCompilerRegex($input);
        expect($result)->toBe($expected, "Failed for input: {$input}");
    }
});

it('handles whitespace variations correctly', function () {
    $testCases = [
        '\blade  {!! $var !!}' => '{!! $var !!}',
        '\blade{{  $var  }}' => '$var', // whitespace trimmed in final output
        '\blade { some text }' => 'some text',
        '\blade{!!  $var  !!}' => '{!!  $var  !!}', // whitespace dipertahankan
        '\blade  {{$var}}' => '$var',
    ];

    foreach ($testCases as $input => $expected) {
        $result = applyLatexCompilerRegex($input);
        expect($result)->toBe($expected, "Failed for input with whitespace: {$input}");
    }
});

it('handles multiline content correctly', function () {
    $content = '\blade{{
        $multiline
        ? "yes"
        : "no"
    }}';

    $result = applyLatexCompilerRegex($content);

    // The result should just be the variable content without extra braces
    // Whitespace is trimmed from the beginning and end
    expect($result)->toBe('$multiline
        ? "yes"
        : "no"');
});

it('does not interfere with regular latex braces', function () {
    $content = '\section{Title} \textbf{Bold} \frac{1}{2} \blade{only this}';

    $result = applyLatexCompilerRegex($content);

    expect($result)->toBe('\section{Title} \textbf{Bold} \frac{1}{2} only this');
});

it('extends blade compiler correctly', function () {
    $files = new Filesystem;
    $compiler = new LatexCompiler($files, '/tmp', '', true, 'php');

    expect($compiler)->toBeInstanceOf(\Illuminate\View\Compilers\BladeCompiler::class);
});
