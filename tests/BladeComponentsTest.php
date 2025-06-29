<?php

function assertBasicLatexStructure(string $rendered): void
{
    expect($rendered)
        ->toContain('\documentclass{article}')
        ->toContain('\begin{document}')
        ->toContain('\end{document}');
}

describe('LaTeX Blade Components', function () {

    it('renders basic component with conditional subsections', function () {
        $testData = [
            'intro' => 'This is the introduction section content.',
            'conclusion' => 'This is the conclusion section content.',
        ];

        $rendered = view('latex.component-test', $testData)->render();

        // Assert basic LaTeX structure
        assertBasicLatexStructure($rendered);

        // Assert section content
        expect($rendered)
            ->toContain('\section{Introduction}')
            ->toContain('This is the introduction section content.')
            ->toContain('\subsection{Overview}')
            ->toContain('\subsection{Goals}')
            ->toContain('\section{Conclusion}')
            ->toContain('This is the conclusion section content.');

        // Verify conditional rendering: only Introduction has subsections
        $subsectionCount = substr_count($rendered, '\subsection{');
        expect($subsectionCount)->toBe(2, 'Only Introduction section should have subsections');
    });

    it('handles dynamic data binding in components', function () {
        $testData = [
            'stats' => [
                ['name' => 'Total Users', 'value' => '1,234'],
                ['name' => 'Active Sessions', 'value' => '89'],
                ['name' => 'Revenue', 'value' => '$12,345'],
            ],
        ];

        $rendered = view('latex.dynamic-component-test', $testData)->render();

        // Assert document structure
        assertBasicLatexStructure($rendered);

        // Assert component content
        expect($rendered)->toContain('\section{Statistics}');

        // Verify all items are rendered correctly
        foreach ($testData['stats'] as $stat) {
            expect($rendered)->toContain($stat['name'].': '.$stat['value']);
        }
    });

    it('supports nested component composition', function () {
        $rendered = view('latex.nested-component-test')->render();

        // Assert document structure
        assertBasicLatexStructure($rendered);

        // Assert nested component content
        expect($rendered)
            ->toContain('\section{Main Section}')
            ->toContain('This is the main content.')
            ->toContain('\subsection{Sub Section}')
            ->toContain('This is sub content.');
    });

});
