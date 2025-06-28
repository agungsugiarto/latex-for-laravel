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

All PHP/Blade code must be wrapped in `\blade{}` commands:

##### Variables
```latex
% Escaped output
\title{\blade{{ $title }}}

% Unescaped output  
\section{\blade{!! $content !!}}
```

##### Control Structures
```latex
\blade{@if($condition)}
\section{\blade{{ $sectionTitle }}}
\blade{@endif}

\blade{@foreach($items as $item)}
\item \blade{{ $item['name'] }}
\blade{@endforeach}
```

##### Comments and Complex Logic
```latex
\blade{@php
    $processedData = processData($rawData);
@endphp}

\blade{!! $processedData !!}
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
