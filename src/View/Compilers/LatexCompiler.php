<?php

namespace Agnula\LatexForLaravel\View\Compilers;

use Illuminate\View\Compilers\BladeCompiler;

final class LatexCompiler extends BladeCompiler
{
    /**
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

        // Transform \blade directives to avoid Blade interference
        
        // \blade{!! ... !!} → {!! ... !!}
        $contents = preg_replace('/\\\\blade\s*({!!\s*.*?\s*!!})/s', '$1', $contents);

        // \blade{{ ... }} → temporary marker to preserve LaTeX braces
        $contents = preg_replace('/\\\\blade\s*{{\s*(.*?)\s*}}/s', '###BLADE_ECHO_START###$1###BLADE_ECHO_END###', $contents);

        // \blade{...} → literal (but exclude cases with {{ or {!!)
        $contents = preg_replace_callback('/\\\\blade\s*{(?!\{|!!)([^}]*?)}/s', function ($matches) {
            return trim($matches[1]);
        }, $contents);

        // Compile with Blade
        $contents = $this->compileString($contents);

        // Restore markers to proper PHP without extra braces
        $contents = preg_replace('/###BLADE_ECHO_START###(.*?)###BLADE_ECHO_END###/', '<?php echo e($1); ?>', $contents);

        if (! empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $this->ensureCompiledDirectoryExists(
            $compiledPath = $this->getCompiledPath($this->getPath())
        );

        $this->files->put($compiledPath, $contents);
    }
}
