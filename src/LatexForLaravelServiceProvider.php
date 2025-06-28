<?php

namespace Agnula\LatexForLaravel;

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;
use Agnula\LatexForLaravel\View\ViewMixinLatex;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\View;

final class LatexForLaravelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->registerLaterCompiler();
        $this->registerLatexEngine();

        $this->app->afterResolving('view', fn (Factory $view) => $view->addExtension('blade.tex', 'latex'));
    }

    /**
     * Register the Latex compiler implementation.
     *
     * @return void
     */
    public function registerLaterCompiler()
    {
        $this->app->singleton('latex.compiler', function ($app) {
            Factory::mixin(new ViewMixinLatex);
            View::mixin(new ViewMixinLatex);

            return tap(new LatexCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            ), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }

    /**
     * Register the Latex engine implementation.
     *
     * @return void
     */
    public function registerLatexEngine()
    {
        $this->app->afterResolving('view.engine.resolver', function (EngineResolver $resolver) {
            $resolver->register('latex', function () {
                $compiler = new CompilerEngine(
                    $this->app->make('latex.compiler'),
                    $this->app->make('files'),
                );

                $this->app->terminating(static function () use ($compiler) {
                    $compiler->forgetCompiledOrNotExpired();
                });

                return $compiler;
            });
        });
    }
}
