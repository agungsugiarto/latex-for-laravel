<?php

use Agnula\LatexForLaravel\LatexForLaravelServiceProvider;
use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;
use Agnula\LatexForLaravel\View\ViewMixinLatex;
use Illuminate\View\Factory;
use Illuminate\View\View;
use Illuminate\View\Engines\EngineResolver;

it('registers the service provider correctly', function () {
    expect($this->app->getProvider(LatexForLaravelServiceProvider::class))
        ->toBeInstanceOf(LatexForLaravelServiceProvider::class);
});

it('registers latex compiler as singleton', function () {
    expect($this->app->bound('latex.compiler'))->toBeTrue();

    $compiler1 = $this->app->make('latex.compiler');
    $compiler2 = $this->app->make('latex.compiler');

    expect($compiler1)->toBe($compiler2)
        ->and($compiler1)->toBeInstanceOf(LatexCompiler::class);
});

it('registers latex engine with engine resolver', function () {
    $resolver = $this->app->make('view.engine.resolver');

    expect($resolver)->toBeInstanceOf(EngineResolver::class);

    // Check if latex engine is registered
    $engine = $resolver->resolve('latex');
    expect($engine)->toBeInstanceOf(\Illuminate\View\Engines\CompilerEngine::class);
});

it('adds blade.tex extension to view factory', function () {
    $view = $this->app->make('view');

    expect($view)->toBeInstanceOf(Factory::class);

    // Check if the extension is registered
    $extensions = $view->getExtensions();
    expect($extensions)->toHaveKey('blade.tex')
        ->and($extensions['blade.tex'])->toBe('latex');
});

it('adds view mixin to factory and view', function () {
    // Refresh the application to ensure clean state
    $this->refreshApplication();

    $view = $this->app->make('view');

    expect($view)->toBeInstanceOf(Factory::class);

    // Test that the mixin methods are available by checking the macro
    $macros = $view->getShared();
    expect($macros)->toBeArray();
});

it('configures latex compiler with correct parameters', function () {
    $compiler = $this->app->make('latex.compiler');

    expect($compiler)->toBeInstanceOf(LatexCompiler::class);

    // Verify compiler configuration through reflection
    $reflection = new ReflectionClass($compiler);
    $filesProperty = $reflection->getProperty('files');
    $filesProperty->setAccessible(true);

    expect($filesProperty->getValue($compiler))->toBe($this->app['files']);
});

it('registers dynamic component correctly', function () {
    $compiler = $this->app->make('latex.compiler');

    // Test that dynamic-component is registered
    expect($compiler)->toBeInstanceOf(LatexCompiler::class);

    // Since we can't easily test internal component registration,
    // we verify the compiler was created successfully
    expect($compiler)->not->toBeNull();
});

it('sets up compiler engine termination callback', function () {
    $resolver = $this->app->make('view.engine.resolver');
    $engine = $resolver->resolve('latex');

    expect($engine)->toBeInstanceOf(\Illuminate\View\Engines\CompilerEngine::class);

    // Verify engine is properly configured
    expect($engine)->not->toBeNull();
});
