# LaTeX Template Test Files

This directory contains reusable `.blade.tex` template files for testing the LaTeX-for-Laravel package.

## Templates

### `government-letter.blade.tex`
A comprehensive real-world government document template featuring:
- Complex document structure with multiple sections
- Advanced LaTeX formatting (tables, minipage, etc.)
- Mixed Blade directives (escaped and unescaped)
- Nested object properties (`$desa->kabupaten`)
- Null coalescing operators (`?? '-'`)
- **37 escaped variables** and **5 unescaped variables**

Used in: `RealWorldTemplateTest.php`

### `simple-document.blade.tex`
A basic LaTeX document template featuring:
- Standard document structure
- Simple Blade variable substitution
- Mixed content types (title, author, data)
- Bug test cases

Used in: `LatexViewRenderTest.php`, `TemplateUseCaseTest.php`

### `bug-test.blade.tex`
A focused template for testing specific bug scenarios:
- The original kabupaten bug case
- Various Blade directive patterns
- Edge cases with `\textbf{}` and other LaTeX commands

Used in: `LatexViewRenderTest.php`, `TemplateUseCaseTest.php`

## Usage in Tests

Instead of creating temporary files in test methods, these templates provide:

1. **Consistency**: Same templates across multiple tests
2. **Maintainability**: Easy to update templates without touching test code
3. **Reusability**: Templates can be used by multiple test cases
4. **Real-world scenarios**: Actual document templates that could be used in production

## Template Structure

All templates include:
```latex
\newcommand{\blade}[1]{}  % Compatibility command for LaTeX compilation
```

This ensures the templates can be compiled directly with LaTeX tools for validation while still working with the Blade engine.

## Adding New Templates

When adding new templates:
1. Include the `\newcommand{\blade}[1]{}` compatibility line
2. Use descriptive filenames ending in `.blade.tex`
3. Document the template purpose and usage in this README
4. Create corresponding test cases that use the template
