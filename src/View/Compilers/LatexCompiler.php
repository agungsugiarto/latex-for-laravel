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

        // \blade{!! ... !!} → {!! ... !!}
        $contents = preg_replace_callback('/\\\\blade\s*{!!\s*(.*?)\s*!!}/s', function ($matches) {
            return "{!! {$matches[1]} !!}";
        }, $contents);

        // \blade{{ ... }} → {{ ... }}
        $contents = preg_replace_callback('/\\\\blade\s*{{\s*(.*?)\s*}}/s', function ($matches) {
            return "{{ {$matches[1]} }}";
        }, $contents);

        // \blade{...} → literal
        $contents = preg_replace_callback('/\\\\blade\s*{(.*?)}/s', function ($matches) {
            return trim($matches[1]);
        }, $contents);

        $contents = $this->compileString($contents);

        if (! empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $this->ensureCompiledDirectoryExists(
            $compiledPath = $this->getCompiledPath($this->getPath())
        );

        $this->files->put($compiledPath, $contents);
    }
}
