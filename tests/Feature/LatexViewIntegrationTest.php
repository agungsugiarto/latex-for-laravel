<?php

it('can create and compile latex views', function () {
    // Create the view
    $view = view('latex.test', [
        'title' => 'Test Document',
        'author' => 'John Doe',
        'date' => '2025-06-29',
        'content' => 'This is a test content.',
    ]);

    expect($view)->toBeInstanceOf(\Illuminate\View\View::class);

    // Test that the view can be rendered
    $rendered = $view->render();

    expect($rendered)->toContain('Test Document')
        ->and($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('This is a test content.');
});

it('processes blade directives correctly in latex views', function () {
    $view = view('latex.blade-test', [
        'title' => 'Blade Test',
        'author' => 'Test Author',
        'date' => '2025-06-29',
        'introduction' => 'This is the introduction.',
        'showConditional' => true,
        'items' => ['Item 1', 'Item 2', 'Item 3'],
    ]);

    $rendered = $view->render();

    expect($rendered)->toContain('Blade Test')
        ->and($rendered)->toContain('Test Author')
        ->and($rendered)->toContain('This is the introduction.')
        ->and($rendered)->toContain('This content is shown conditionally.')
        ->and($rendered)->toContain('Item 1')
        ->and($rendered)->toContain('Item 2')
        ->and($rendered)->toContain('Item 3');
});

it('can use view extension blade.tex', function () {
    // Should be able to find the view with .blade.tex extension
    expect(view()->exists('latex.extension-test'))->toBeTrue();

    $view = view('latex.extension-test');
    $rendered = $view->render();

    expect($rendered)->toContain('Extension Test')
        ->and($rendered)->toContain('Hello World!');
});
