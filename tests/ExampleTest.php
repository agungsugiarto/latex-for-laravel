<?php

it('has a working test suite', function () {
    expect(true)->toBeTrue();
});

it('can access application instance', function () {
    expect($this->app)->toBeInstanceOf(\Illuminate\Foundation\Application::class);
});

it('loads the service provider', function () {
    $providers = $this->app->getLoadedProviders();

    expect($providers)->toHaveKey('Agnula\LatexForLaravel\LatexForLaravelServiceProvider');
});

it('registers latex services', function () {
    expect($this->app->bound('latex.compiler'))->toBeTrue();
});

test('example test with traditional syntax', function () {
    $this->assertTrue(true);
});
