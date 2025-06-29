<?php

use Agnula\LatexForLaravel\View\Compilers\LatexCompiler;

beforeEach(function () {
    setupMathExtension();
});

/**
 * Setup math extension based on documentation example
 */
function setupMathExtension()
{
    $compiler = app('latex.compiler');

    // Process \blademath{} directives
    $compiler->addProcessor(function ($content, $next) {
        // Convert \blademath{expression} to markers
        $content = preg_replace(
            '/\\\\blademath\s*{(.*?)}/s',
            '###MATH_START###$1###MATH_END###',
            $content
        );

        return $next($content);
    });

    // Restore math markers to specialized PHP
    $compiler->addRestorer(function ($content, $next) {
        $content = preg_replace(
            '/###MATH_START###(.*?)###MATH_END###/',
            '<?php echo "MATH_RENDERED[$1]"; ?>',
            $content
        );

        return $next($content);
    });
}

it('can register math extension processors', function () {
    $compiler = app('latex.compiler');

    expect($compiler)->toBeInstanceOf(LatexCompiler::class);

    // The setupMathExtension() in beforeEach should have registered processors
    // Test that we can add more processors
    $result = $compiler->addProcessor(function ($content, $next) {
        return $next($content);
    });

    expect($result)->toBe($compiler);
});

it('renders math extension template correctly', function () {
    $data = [
        'title' => 'Mathematical Document',
        'equations' => [
            (object) ['description' => 'Pythagorean theorem', 'formula' => 'a^2 + b^2 = c^2'],
            (object) ['description' => 'Einstein equation', 'formula' => 'E = mc^2'],
        ],
    ];

    $rendered = view('latex.math-extension', $data)->render();

    expect($rendered)
        ->toContain('Mathematical Document')
        ->toContain('MATH_RENDERED[x^2 + y^2 = z^2]')
        ->toContain('Pythagorean theorem')
        ->toContain('Einstein equation')
        ->toContain('MATH_RENDERED[<?php echo e(a^2 + b^2 = c^2); ?>]') // Variable should be processed by Blade
        ->toContain('MATH_RENDERED[<?php echo e(E = mc^2); ?>]'); // Variable should be processed by Blade
});

it('integrates with blade template system', function () {
    $data = ['title' => 'Simple Math Document'];

    $rendered = view('latex.simple-math', $data)->render();

    expect($rendered)
        ->toContain('Simple Math Document')
        ->toContain('MATH_RENDERED[E = mc^2]');
});
