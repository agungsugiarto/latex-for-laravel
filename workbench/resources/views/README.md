# LaTeX Template Testing Structure

This directory contains the refactored test structure for the LaTeX for Laravel package.

## Structure Overview

```
workbench/
└── resources/
    └── views/
        └── latex/
            ├── simple-document.blade.tex
            ├── bug-test.blade.tex
            ├── output-test.blade.tex
            ├── complex-output-test.blade.tex
            ├── php-code-test.blade.tex
            ├── include-test.blade.tex
            ├── government-letter.blade.tex
            └── partials/
                ├── header.blade.tex
                └── footer.blade.tex
```

## Key Improvements

### 1. **Eliminated Setup/Teardown**
- No more `file_put_contents()` and `unlink()` in tests
- Templates are pre-created and persist between test runs
- Tests run faster with less I/O operations

### 2. **Structured Template Organization**
- Templates are organized under `workbench/resources/views/latex/`
- Follows Laravel's standard view structure
- Supports subdirectories and partials

### 3. **Enhanced TestCase**
- `TestCase` class provides common test data methods
- View paths are automatically configured
- Backward compatibility with legacy template directory

### 4. **Reusable Test Data**
- `getSimpleDocumentData()` - Basic document test data
- `getBugTestData()` - Bug test case data
- `getComplexDocumentData()` - Complex document structures
- `getPhpCodeTestData()` - PHP code block test data
- `getGovernmentLetterData()` - Government letter template data

### 5. **Template Usage**
Instead of:
```php
// Old way - create file in test
$templatePath = __DIR__.'/templates/output-test.blade.tex';
$templateContent = '...';
file_put_contents($templatePath, $templateContent);
// ... test code ...
unlink($templatePath);
```

Now:
```php
// New way - use pre-created template
$rendered = view('latex.output-test', $data)->render();
```

## Template Naming Convention

Templates use the `latex.` prefix to organize them under the `latex` subdirectory:
- `latex.simple-document` → `workbench/resources/views/latex/simple-document.blade.tex`
- `latex.government-letter` → `workbench/resources/views/latex/government-letter.blade.tex`
- `latex.partials.header` → `workbench/resources/views/latex/partials/header.blade.tex`

## Available Templates

### Core Templates
- **simple-document**: Basic document with user data
- **bug-test**: Bug testing scenarios
- **output-test**: Simple output compilation
- **complex-output-test**: Complex nested structures
- **php-code-test**: PHP code blocks and variables
- **include-test**: Include and yield directives
- **government-letter**: Official letter template

### Partials
- **header**: Common header with packages
- **footer**: Common footer with date

## Usage Examples

### Basic Template Usage
```php
$data = $this->getSimpleDocumentData();
$rendered = view('latex.simple-document', $data)->render();
```

### Government Letter
```php
$data = $this->getGovernmentLetterData();
$rendered = view('latex.government-letter', $data)->render();
```

### Complex Document
```php
$data = $this->getComplexDocumentData();
$rendered = view('latex.complex-output-test', $data)->render();
```

## Benefits

1. **Faster Tests**: No file I/O operations during test execution
2. **Cleaner Code**: Tests focus on logic, not file management
3. **Better Organization**: Templates are structured and reusable
4. **Maintainability**: Easy to update templates across all tests
5. **Reliability**: No risk of file cleanup failures affecting other tests
6. **Laravel Standards**: Follows Laravel's view structure conventions

## Migration Guide

When migrating existing tests:

1. Move template content from `file_put_contents()` to `.blade.tex` files
2. Replace `$this->app['view']->addLocation(__DIR__.'/templates')` with template references
3. Use `$this->getXxxData()` methods instead of inline data arrays
4. Replace `view('template-name')` with `view('latex.template-name')`
5. Remove `unlink()` cleanup calls
6. Extend `TestCase` class for automatic setup
