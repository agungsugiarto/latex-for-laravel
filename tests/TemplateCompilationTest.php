<?php

it('handles tex-string compilation output', function () {
    // Use pre-configured test data
    $data = [
        'title' => 'Test Document',
        'author' => 'Test Author',
    ];

    // Test tex-string output by checking that view renders correctly
    $rendered = view('latex.output-test', $data)->render();

    expect($rendered)
        ->toContain('Test Document')
        ->toContain('Test Author')
        ->toContain('\documentclass{article}')
        ->toContain('Simple test document content.')
        ->not->toContain('\blade{{')
        ->not->toContain('\blade{!!');
});

it('processes complex template structure correctly', function () {
    // Use pre-configured test data
    $data = $this->getComplexDocumentData();

    $rendered = view('latex.complex-output-test', $data)->render();

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
});

it('handles template with php code blocks', function () {
    // Use pre-configured test data
    $data = $this->getPhpCodeTestData();

    $rendered = view('latex.php-code-test', $data)->render();

    expect($rendered)
        ->toContain('PHP Code Test')
        ->toContain('Processed: HELLO WORLD')
        ->toContain('Item 1: First')
        ->toContain('Item 2: Second')
        ->toContain('Item 3: Third')
        ->not->toContain('@php')
        ->not->toContain('@endphp');
});

it('handles template with includes', function () {
    $data = [
        'title' => 'Include Test Document',
    ];

    $rendered = view('latex.include-test', $data)->render();

    expect($rendered)
        ->toContain('Include Test Document')
        ->toContain('% Header content from partial')
        ->toContain('% Footer content from partial')
        ->toContain('\usepackage{amsmath}')
        ->toContain('\usepackage{graphicx}')
        ->toContain('Generated on')
        ->not->toContain('@include');
});
