<?php

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;
use Illuminate\Support\ServiceProvider;

/**
 * Mock service provider for testing extension pattern
 */
class TestLatexExtensionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->extendLatexCompiler();
    }

    private function extendLatexCompiler()
    {
        // Get the compiler instance using the service container binding
        $compiler = $this->app->make('latex.compiler');

        // Add processor for academic citations
        $compiler->addProcessor(function ($content, $next) {
            // Convert \acadcite{author}{year} to proper LaTeX citation
            $content = preg_replace(
                '/\\\\acadcite\s*{([^}]+)}\s*{([^}]+)}/',
                '\\cite{$1} ($2)',
                $content
            );

            return $next($content);
        });

        // Add processor for dynamic table generation
        $compiler->addProcessor(function ($content, $next) {
            // Process \bladetable{} for complex table structures
            $content = preg_replace(
                '/\\\\bladetable\s*{(.*?)}/s',
                'TABLE_MARKER[$1]',
                $content
            );

            return $next($content);
        });
    }
}

it('can register service provider extension', function () {
    // Register the test service provider
    $provider = new TestLatexExtensionServiceProvider($this->app);
    $provider->boot();

    $compiler = app('latex.compiler');

    expect($compiler)->toBeInstanceOf(LatexCompiler::class);
});

it('processes academic citations correctly through service provider', function () {
    // Register the extension
    $provider = new TestLatexExtensionServiceProvider($this->app);
    $provider->boot();

    $result = view('latex.citation-test')->render();

    expect($result)
        ->toContain('\cite{Smith} (2023)')
        ->toContain('\cite{Doe} (2024)')
        ->not->toContain('\acadcite{');
});

it('processes table generation through service provider', function () {
    // Register the extension
    $provider = new TestLatexExtensionServiceProvider($this->app);
    $provider->boot();

    $result = view('latex.table-test')->render();

    expect($result)->toContain('TABLE_MARKER[header1,header2,header3]');
});

it('renders template with service provider extensions', function () {
    // Register the extension
    $provider = new TestLatexExtensionServiceProvider($this->app);
    $provider->boot();

    $data = [
        'title' => 'Research Document',
    ];

    $rendered = view('latex.service-provider-test', $data)->render();

    expect($rendered)
        ->toContain('Research Document')
        ->toContain('\cite{Johnson} (2023)')
        ->toContain('\cite{Smith} (2021)')
        ->toContain('\cite{Brown} (2022)')
        ->toContain('TABLE_MARKER[Name,Age,Score]');
});

it('handles multiple service provider extensions', function () {
    // First extension
    $provider1 = new TestLatexExtensionServiceProvider($this->app);
    $provider1->boot();

    // Second extension with different functionality
    $compiler = app('latex.compiler');
    $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('\highlight{', '\textbf{\textcolor{yellow}{', $content);
        $content = str_replace('}highlight', '}}', $content);

        return $next($content);
    });

    $result = view('latex.multi-extension-test')->render();

    expect($result)
        ->toContain('\cite{Author} (2023)')
        ->toContain('\textbf{\textcolor{yellow}{important text}}');
});

it('allows chaining of custom processors in service provider', function () {
    $compiler = app('latex.compiler');

    // Simulate service provider registration with multiple processors
    $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('\step1{', '\textbf{', $content);

        return $next($content);
    });

    $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('}step1', '}', $content);

        return $next($content);
    });

    $compiler->addProcessor(function ($content, $next) {
        $content = str_replace('\step2{', '\textit{', $content);

        return $next($content);
    });

    $result = view('latex.chaining-test')->render();

    expect($result)
        ->toContain('\textbf{bold text}')
        ->toContain('\textit{italic text}');
});
