# LaTeX for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/agungsugiarto/latex-for-laravel.svg?style=flat-square)](https://packagist.org/packages/agungsugiarto/latex-for-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/agungsugiarto/latex-for-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/agungsugiarto/latex-for-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/agungsugiarto/latex-for-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/agungsugiarto/latex-for-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/agungsugiarto/latex-for-laravel.svg?style=flat-square)](https://packagist.org/packages/agungsugiarto/latex-for-laravel)

A Laravel package that provides seamless integration between Laravel Blade templates and LaTeX, enabling you to create dynamic PDF documents using familiar Blade syntax while maintaining valid LaTeX structure.

## Features

- 🔥 **Blade + LaTeX Integration**: Use Laravel Blade directives within LaTeX templates
- 📄 **Valid LaTeX Structure**: Templates remain compilable with standard LaTeX compilers
- 🎯 **Multiple Output Formats**: Generate PDFs, download source files, or return content as strings
- 🚀 **Easy to Use**: Simple API with powerful customization options
- ⚡ **Pipeline-Based Processing**: Clean, extensible compilation using Laravel Pipeline
- 🔌 **Extensible**: Add custom processors and restorers for specialized LaTeX needs
- 🧪 **Well Tested**: Comprehensive test suite ensuring reliability

## Support us

[![Support via Saweria](https://img.shields.io/badge/Support-Saweria-orange?style=flat-square)](https://saweria.co/agungsugiarto)

If this package helps you in your projects, consider supporting the development by [buying me a coffee on Saweria](https://saweria.co/agungsugiarto). 

Your support helps maintain and improve this package for the Laravel community. ☕️

## Installation

You can install the package via composer:

```bash
composer require agungsugiarto/latex-for-laravel
```

The package will automatically register its service provider.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- `pdflatex` installed on your system (for PDF compilation)

## Quick Start

Create a LaTeX template file `resources/views/invoice.blade.tex`:

```latex
\documentclass{article}
\usepackage[utf8]{inputenc}
\newcommand{\blade}[1]{}
\begin{document}

\title{\blade{{ $title }}}
\author{\blade{{ $author }}}
\maketitle

\blade{@foreach($items as $item)}
\section{\blade{{ $item['name'] }}}
\blade{{ $item['description'] }}
\blade{@endforeach}

\end{document}
```

Use it in your controller:

```php
// Generate and download PDF
return view('invoice', [
    'title' => 'Monthly Invoice',
    'author' => 'John Doe',
    'items' => [
        ['name' => 'Service 1', 'description' => 'Description here'],
        ['name' => 'Service 2', 'description' => 'Another description'],
    ]
])->compile('invoice.pdf', 'download');
```

## LaTeX Template Structure

All `.blade.tex` files must follow this structure to ensure they are valid LaTeX documents that can be compiled directly while still supporting Laravel Blade directives.

### Required Structure

#### 1. Include the `\newcommand{\blade}[1]{}` definition

Every LaTeX template must include this command in the preamble:

```latex
\documentclass{article}
\usepackage[utf8]{inputenc}
\newcommand{\blade}[1]{}
\begin{document}
% Your content here
\end{document}
```

#### 2. Wrap all Blade directives in `\blade{}`

All PHP/Blade code must be wrapped in `\blade{}` commands. There are three forms of blade directives:

##### Escaped Output: `\blade{{ expression }}`
For safe output of variables that should be escaped to prevent LaTeX compilation issues:
```latex
% Variable output
\title{\blade{{ $title }}}
\author{\blade{{ $author->name }}}

% Method calls and complex expressions  
\section{\blade{{ $document->getFormattedTitle() }}}
\date{\blade{{ now()->format('Y-m-d') }}}
```

##### Unescaped Output: `\blade{!! expression !!}`
For raw LaTeX content that should be rendered without escaping:
```latex
% Raw LaTeX commands
\blade{!! $latexCommands !!}

% Pre-formatted LaTeX content
\blade{!! '\textbf{Bold} and \textit{italic} text' !!}

% Complex LaTeX structures
\blade{!! $generatedTableContent !!}
```

##### Literal Content: `\blade{content}`
For Blade directives, PHP code, and other content that should pass through directly:
```latex
% Control structures
\blade{@if($condition)}
\section{Conditional Section}
\blade{@endif}

\blade{@foreach($items as $item)}
\item \blade{{ $item['name'] }}: \blade{{ $item['description'] }}
\blade{@endforeach}

% PHP code blocks
\blade{@php
    $processedData = processData($rawData);
    $formattedOutput = formatForLaTeX($processedData);
@endphp}

% Blade components and includes
\blade{@include('partials.latex-header')}
\blade{@yield('content')}
```

### Complete Example

```latex
\documentclass{article}
\usepackage[utf8]{inputenc}
\usepackage{amsmath}
\usepackage{graphicx}
\newcommand{\blade}[1]{}

\begin{document}

\title{\blade{{ $title }}}
\author{\blade{{ $author }}}
\date{\blade{{ $date }}}
\maketitle

\tableofcontents

\blade{@foreach($sections as $section)}
\section{\blade{{ $section['title'] }}}
\blade{{ $section['content'] }}

\blade{@if(isset($section['subsections']))}
\blade{@foreach($section['subsections'] as $subsection)}
\subsection{\blade{{ $subsection['title'] }}}
\blade{!! $subsection['content'] !!}
\blade{@endforeach}
\blade{@endif}

\blade{@endforeach}

\blade{@if($includeReferences)}
\bibliography{\blade{{ $bibliographyFile }}}
\blade{@endif}

\end{document}
```

### Benefits of This Structure

1. **Valid LaTeX**: The file can be compiled directly with `pdflatex` without processing Blade directives
2. **Blade Processing**: Laravel can process all `\blade{}` commands to generate dynamic content
3. **IDE Support**: LaTeX editors will recognize the file as valid LaTeX and provide syntax highlighting
4. **Version Control**: The files are readable and diffable in version control systems

## Pipeline Processing Architecture

The package uses a modern pipeline-based approach for processing LaTeX templates, providing clean separation of concerns and extensibility for custom use cases.

### How It Works

The compilation process follows these steps:

1. **Blade Directive Processing**: Using Laravel Pipeline, the compiler processes different types of `\blade{}` directives:
   - `\blade{!! raw content !!}` → Direct unescaped output
   - `\blade{{ expression }}` → Escaped output (converted to markers, then restored as PHP echo statements)
   - `\blade{literal content}` → Direct literal content (for Blade directives, PHP code, etc.)

2. **Blade Compilation**: Standard Laravel Blade compilation processes the resulting template

3. **Marker Restoration**: Another pipeline restores any temporary markers to proper PHP code

### Built-in Processors

#### Raw Content Processor
Handles unescaped LaTeX content:
```latex
\blade{!! $latexCommands !!}      % → {!! $latexCommands !!}
\blade{!! '\textbf{Bold Text}' !!} % → {!! '\textbf{Bold Text}' !!}
```

#### Echo Expression Processor  
Handles escaped variable output:
```latex
\blade{{ $documentTitle }}         % → <?php echo e($documentTitle); ?>
\blade{{ $author->name }}          % → <?php echo e($author->name); ?>
\blade{{ config('latex.class') }}  % → <?php echo e(config('latex.class')); ?>
```

#### Literal Content Processor
Handles Blade directives and PHP code:
```latex
\blade{@if($condition)}            % → @if($condition)
\blade{@foreach($items as $item)}  % → @foreach($items as $item)
\blade{<?php echo $var; ?>}        % → <?php echo $var; ?>
\blade{@yield('content')}          % → @yield('content')
```

### Extending the Pipeline

You can add custom processors and restorers to handle specialized LaTeX needs:

#### Adding Custom Processors

```php
// Get the compiler instance using the service container binding
$compiler = app('latex.compiler');

// Add a custom processor for mathematical expressions
$compiler->addProcessor(function($content, $next) {
    // Process custom \math{} directives
    $content = preg_replace(
        '/\\\\math\s*{(.*?)}/s', 
        '\\begin{equation}$1\\end{equation}', 
        $content
    );
    
    return $next($content);
});

// Add a processor for custom bibliography handling
$compiler->addProcessor(function($content, $next) {
    // Process \bibref{} to \cite{}
    $content = str_replace('\bibref{', '\cite{', $content);
    
    return $next($content);
});
```

#### Adding Custom Restorers

```php
// Get the compiler instance using the service container binding
$compiler = app('latex.compiler');

// Add a custom restorer for special markers
$compiler->addRestorer(function($content, $next) {
    // Restore custom markers to PHP code
    $content = preg_replace(
        '/###CUSTOM_MATH_START###(.*?)###CUSTOM_MATH_END###/',
        '<?php echo $mathHelper->render("$1"); ?>',
        $content
    );
    
    return $next($content);
});
```

#### Complete Custom Extension Example

```php
<?php

use Illuminate\Support\ServiceProvider;

class LaTeXMathExtensionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerMathExtension();
    }
    
    private function registerMathExtension()
    {
        // Get the compiler instance using the service container binding
        $compiler = $this->app->make('latex.compiler');
        
        // Process \blademath{} directives
        $compiler->addProcessor(function($content, $next) {
            // Convert \blademath{expression} to markers
            $content = preg_replace(
                '/\\\\blademath\s*{(.*?)}/s',
                '###MATH_START###$1###MATH_END###',
                $content
            );
            
            return $next($content);
        });
        
        // Restore math markers to specialized PHP
        $compiler->addRestorer(function($content, $next) {
            $content = preg_replace(
                '/###MATH_START###(.*?)###MATH_END###/',
                '<?php echo $this->renderMath("$1"); ?>',
                $content
            );
            
            return $next($content);
        });
    }
}
```

Register the service provider in your `config/app.php`:

```php
'providers' => [
    // Other providers...
    App\Providers\LaTeXMathExtensionServiceProvider::class,
],
```

#### Usage in Templates

After registering the extension:

```latex
\documentclass{article}
\usepackage{amsmath}
\newcommand{\blade}[1]{}
\newcommand{\blademath}[1]{}

\begin{document}

\title{\blade{{ $title }}}

% Using the custom math processor
\blademath{x^2 + y^2 = z^2}

% Standard blade directives still work
\blade{@foreach($equations as $eq)}
\blade{{ $eq->description }}
\blademath{\blade{{ $eq->formula }}}
\blade{@endforeach}

\end{document}
```

## API Reference

### Compilation Methods

The `compile()` method supports various output destinations:

```php
// Display PDF inline in browser
return view('template', $data)->compile('document.pdf', 'inline');

// Force download PDF
return view('template', $data)->compile('document.pdf', 'download');

// Save PDF to storage
view('template', $data)->compile('document.pdf', 'storage');

// Save PDF to storage and display inline
return view('template', $data)->compile('document.pdf', 'storage-inline');

// Save PDF to storage and download
return view('template', $data)->compile('document.pdf', 'storage-download');

// Return PDF content as string
$pdfContent = view('template', $data)->compile('document.pdf', 'string');

// Download LaTeX source file
return view('template', $data)->compile('document.tex', 'tex');

// Return LaTeX source as string
$latexSource = view('template', $data)->compile('document.tex', 'tex-string');

// Save LaTeX source to storage
view('template', $data)->compile('document.tex', 'storage-tex');

// Custom handler with closure
return view('template', $data)->compile('document.pdf', function($view, $pdfContent, $fileName, $texFile) {
    // Custom processing logic
    return response()->json(['success' => true, 'size' => strlen($pdfContent)]);
});
```

### Advanced Usage

#### Using with Blade Components

Create reusable LaTeX components:

```latex
% resources/views/components/latex-section.blade.tex
\newcommand{\blade}[1]{}
\section{\blade{{ $title }}}
\blade{{ $content }}

\blade{@if($includeSubsections)}
\blade{@foreach($subsections as $subsection)}
\subsection{\blade{{ $subsection }}}
\blade{@endforeach}
\blade{@endif}
```

Use in your main template:

```latex
\documentclass{article}
\newcommand{\blade}[1]{}
\begin{document}

\blade{@component('components.latex-section', ['title' => 'Introduction', 'content' => $intro])}
\blade{@endcomponent}

\end{document}
```

#### Working with Complex LaTeX Structures

For advanced LaTeX documents with multiple processors:

```php
// In your controller or service

class DocumentService
{
    public function setupAdvancedCompiler()
    {
        // Get the compiler instance using the service container binding
        $compiler = app('latex.compiler');
        
        // Add mathematical notation processor
        $compiler->addProcessor(function($content, $next) {
            // Convert \bladeformula{} to proper math environments
            $content = preg_replace_callback(
                '/\\\\bladeformula\s*{(.*?)}/s',
                function($matches) {
                    return "\\begin{align}\n" . trim($matches[1]) . "\n\\end{align}";
                },
                $content
            );
            
            return $next($content);
        });
        
        // Add figure handling processor
        $compiler->addProcessor(function($content, $next) {
            // Process dynamic figure inclusion
            $content = preg_replace(
                '/\\\\bladefigure\s*{([^}]+)}\s*{([^}]+)}\s*{([^}]+)}/',
                '###FIG_START###$1|$2|$3###FIG_END###',
                $content
            );
            
            return $next($content);
        });
        
        // Restore figure markers
        $compiler->addRestorer(function($content, $next) {
            $content = preg_replace_callback(
                '/###FIG_START###([^|]+)\|([^|]+)\|([^#]+)###FIG_END###/',
                function($matches) {
                    return '<?php echo $this->renderFigure("' . 
                           trim($matches[1]) . '", "' . 
                           trim($matches[2]) . '", "' . 
                           trim($matches[3]) . '"); ?>';
                },
                $content
            );
            
            return $next($content);
        });
        
        return $compiler;
    }
}
```

##### Example Template for Advanced Processors

```latex
\documentclass{article}
\usepackage{amsmath}
\usepackage{graphicx}
\newcommand{\blade}[1]{}
\newcommand{\bladeformula}[1]{}
\newcommand{\bladefigure}[3]{}

\begin{document}

\title{\blade{{ $title }}}

% Using mathematical notation processor
\bladeformula{E = mc^2}

% Using figure processor (path, caption, label)
\bladefigure{images/chart.png}{Sales Chart}{fig:sales}

% Standard blade directives still work
\blade{@foreach($formulas as $formula)}
\bladeformula{\blade{{ $formula->latex }}}
\blade{@endforeach}

\end{document}
```

#### Error Handling

```php
try {
    return view('report', $data)->compile('report.pdf', 'download');
} catch (\Illuminate\Process\Exceptions\ProcessFailedException $e) {
    // Handle LaTeX compilation errors
    return response()->json(['error' => 'LaTeX compilation failed'], 500);
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Agung Sugiarto](https://github.com/agungsugiarto)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
