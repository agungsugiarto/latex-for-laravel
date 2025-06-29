<?php

it('can render latex view with blade directives using real Laravel view', function () {
    // Use pre-configured test data
    $data = $this->getSimpleDocumentData();

    // Render the view using the template file
    $rendered = view('latex.simple-document', $data)->render();

    // Assertions - variables now render without extra braces
    expect($rendered)->toContain('My Test Document')
        ->and($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Alice Smith')
        ->and($rendered)->toContain('alice@example.com')
        ->and($rendered)->toContain('95')
        ->and($rendered)->toContain('This is literal content')
        ->and($rendered)->toContain('Terara')
        ->and($rendered)->toContain('\documentclass{article}')
        ->and($rendered)->toContain('\begin{document}')
        ->and($rendered)->toContain('\end{document}')
        ->and($rendered)->not->toContain('\blade{{')
        ->and($rendered)->not->toContain('\blade{!!');
});

it('handles the specific bug case correctly in real Laravel view', function () {
    // Use pre-configured test data
    $data = $this->getBugTestData();

    $rendered = view('latex.bug-test', $data)->render();

    // Should contain "Terara" without extra braces
    expect($rendered)->toContain('Terara')
        ->and($rendered)->toContain('\Large')
        ->and($rendered)->toContain('\textbf')
        ->and($rendered)->not->toContain('\blade{{');
});
