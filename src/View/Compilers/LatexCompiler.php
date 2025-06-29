<?php

namespace Agnula\LatexForLaravel\View\Compilers;

use Illuminate\Pipeline\Pipeline;
use Illuminate\View\Compilers\BladeCompiler;

final class LatexCompiler extends BladeCompiler
{
    /** @var array<\Closure> Custom processors for extending blade directive handling */
    private array $process = [];

    /** @var array<\Closure> Custom restorers for extending marker restoration */
    private array $restorers = [];

    /**
     * Add a custom processor to the blade directive pipeline
     *
     * @param  \Closure  $processor  A closure that accepts ($content, $next) and returns processed content
     * @return self For method chaining
     *
     * @example
     * $compiler->addProcessor(function($content, $next) {
     *     // Custom processing logic here
     *     $content = str_replace('\custom', 'replacement', $content);
     *     return $next($content);
     * });
     */
    public function addProcessor(\Closure $processor): self
    {
        $this->process[] = $processor;

        return $this;
    }

    /**
     * Add a custom restorer to the marker restoration pipeline
     *
     * @param  \Closure  $restorer  A closure that accepts ($content, $next) and returns restored content
     * @return self For method chaining
     *
     * @example
     * $compiler->addRestorer(function($content, $next) {
     *     // Custom restoration logic here
     *     $content = str_replace('###CUSTOM_MARKER###', '<?php echo "custom"; ?>', $content);
     *     return $next($content);
     * });
     */
    public function addRestorer(\Closure $restorer): self
    {
        $this->restorers[] = $restorer;

        return $this;
    }

    /**
     * Compile the view at the given path with LaTeX-aware processing.
     *
     * {@inheritdoc}
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        if ($this->cachePath === null) {
            return;
        }

        $contents = $this->files->get($this->getPath());

        // Process \blade directives using Laravel Pipeline
        $contents = $this->processBladeDirectives($contents);

        // Compile with Blade
        $contents = $this->compileString($contents);

        // Restore markers using Laravel Pipeline
        $contents = $this->restoreProcessedMarkers($contents);

        if (! empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $this->ensureCompiledDirectoryExists(
            $compiledPath = $this->getCompiledPath($this->getPath())
        );

        $this->files->put($compiledPath, $contents);
    }

    /**
     * Process \blade directives using Laravel Pipeline
     */
    private function processBladeDirectives(string $contents): string
    {
        $defaultProcessors = [
            new class
            {
                /**
                 * Process \blade{!! raw content !!} → {!! raw content !!}
                 *
                 * For unescaped LaTeX content that should be rendered as-is. Examples:
                 * - \blade{!! $latexCommands !!} → {!! $latexCommands !!}
                 * - \blade{!! '\textbf{Bold Text}' !!} → {!! '\textbf{Bold Text}' !!}
                 * - \blade{!! $customMacros !!} → {!! $customMacros !!}
                 */
                public function handle(string $content, \Closure $next): string
                {
                    return $next(preg_replace('/\\\\blade\s*({!!\s*.*?\s*!!})/s', '$1', $content));
                }
            },
            new class
            {
                /**
                 * Process \blade{{ expression }} → ###BLADE_ECHO_START###expression###BLADE_ECHO_END###
                 *
                 * For escaped output expressions that will be safely rendered in LaTeX. Examples:
                 * - \blade{{ $documentTitle }} → ###BLADE_ECHO_START###$documentTitle###BLADE_ECHO_END###
                 * - \blade{{ $author->name }} → ###BLADE_ECHO_START###$author->name###BLADE_ECHO_END###
                 * - \blade{{ config('latex.documentclass') }} → ###BLADE_ECHO_START###config('latex.documentclass')###BLADE_ECHO_END###
                 *
                 * Markers will later be restored to: <?php echo e(expression); ?>
                 */
                public function handle(string $content, \Closure $next): string
                {
                    return $next(preg_replace(
                        '/\\\\blade\s*{{\s*(.*?)\s*}}/',
                        '###BLADE_ECHO_START###$1###BLADE_ECHO_END###',
                        $content
                    ));
                }
            },
            new class
            {
                /**
                 * Process \blade{literal content} → literal content
                 *
                 * For embedding Blade directives, PHP code, or other content that should
                 * be passed through directly without escaping. Examples:
                 * - \blade{@if($condition)} → @if($condition)
                 * - \blade{<?php echo $var; ?>} → <?php echo $var; ?>
                 * - \blade{@foreach($items as $item)} → @foreach($items as $item)
                 * - \blade{@yield('content')} → @yield('content')
                 */
                public function handle(string $content, \Closure $next): string
                {
                    return $next(preg_replace_callback(
                        '/\\\\blade\s*{(?!\{|!!)([^}]*?)}/s',
                        fn (array $matches): string => trim($matches[1]),
                        $content
                    ));
                }
            },
        ];

        return app(Pipeline::class)
            ->send($contents)
            ->through([...$defaultProcessors, ...$this->process])
            ->then(fn (string $content): string => $content);
    }

    /**
     * Restore processed markers to proper PHP code using Laravel Pipeline
     */
    private function restoreProcessedMarkers(string $contents): string
    {
        $defaultRestorers = [
            new class
            {
                /**
                 * Restore ###BLADE_ECHO_START###expression###BLADE_ECHO_END### → <?php echo e(expression); ?>
                 */
                public function handle(string $content, \Closure $next): string
                {
                    return $next(preg_replace(
                        '/###BLADE_ECHO_START###(.*?)###BLADE_ECHO_END###/',
                        '<?php echo e($1); ?>',
                        $content
                    ));
                }
            },
        ];

        return app(Pipeline::class)
            ->send($contents)
            ->through([...$defaultRestorers, ...$this->restorers])
            ->then(fn (string $content): string => $content);
    }
}
