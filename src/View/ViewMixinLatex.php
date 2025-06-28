<?php

namespace Agnula\LatexForLaravel\View;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Process\Exceptions\ProcessFailedException;

/**
 * Provides the 'compile' macro for Laravel View instances to render LaTeX as PDF/TeX.
 *
 * Example usage:
 * ```php
 * // Display PDF inline in the browser
 * return view('latex.invoice', $data)->compile('invoice.pdf', 'inline');
 *
 * // Force download the PDF
 * return view('latex.invoice', $data)->compile('invoice.pdf', 'download');
 *
 * // Save PDF to storage
 * view('latex.invoice', $data)->compile('invoice.pdf', 'storage');
 *
 * // Download the .tex source file
 * return view('latex.invoice', $data)->compile('invoice.tex', 'tex');
 * ```
 */
final class ViewMixinLatex
{
    /**
     * Registers the 'compile' macro as a closure for View instances.
     */
    public function compile(): Closure
    {
        /**
         * Compile the view as LaTeX and output or store the result.
         *
         * @param string $fileName    Output file name (default: 'document.pdf').
         * @param string|Closure $destination Output mode:
         *                            - 'inline': Display PDF in browser.
         *                            - 'download': Force download PDF.
         *                            - 'storage': Save PDF to storage.
         *                            - 'storage-inline': Save PDF to storage and display inline.
         *                            - 'storage-download': Save PDF to storage and download.
         *                            - 'string': Return PDF as a string response.
         *                            - 'tex': Download the .tex source.
         *                            - 'tex-string': Return the LaTeX source as a string.
         *                            - 'storage-tex': Save the .tex source to storage.
         * @return \Symfony\Component\HttpFoundation\Response|string|bool
         *
         * @throws ProcessFailedException If LaTeX compilation fails.
         * @throws InvalidArgumentException If the destination is invalid.
         *
         * Case explanations:
         *  - 'inline': Display the generated PDF in the browser.
         *  - 'download': Download the generated PDF as a file.
         *  - 'storage': Save the PDF to the Laravel storage disk.
         *  - 'storage-inline': Save the PDF to storage and show it in the browser.
         *  - 'storage-download': Save the PDF to storage and download it.
         *  - 'string': Return the PDF content as a string response (for API use).
         *  - 'tex': Download the generated LaTeX source file.
         *  - 'tex-string': Return the LaTeX source as plain text (for API/debugging).
         *  - 'storage-tex': Save the LaTeX source to storage.
         */
        return function (string $fileName = 'document.pdf', string|Closure $destination = 'inline') {
            // Render the view as LaTeX
            $latex = $this->render();

            // Use the compiled view path as the base for temp files
            $tempDir = config('view.compiled', sys_get_temp_dir());

            // Get the compiled path for this view, which is a full file path
            $compiledPath = app('latex.compiler')->getCompiledPath($this->getPath());

            // Remove extension for base temp name
            $tempBase = pathinfo($compiledPath, PATHINFO_FILENAME);

            $texFile = $tempDir . DIRECTORY_SEPARATOR . $tempBase . '.tex';
            $pdfFile = $tempDir . DIRECTORY_SEPARATOR . $tempBase . '.pdf';

            File::put($texFile, $latex);

            $callerViewDir = dirname(app('view')->getFinder()->find($this->name()));

            $texinputs = collect(config('filesystems.disks'))
                ->filter(fn ($disk) => ($disk['driver'] ?? null) === 'local' && isset($disk['root']))
                ->pluck('root')
                ->push($callerViewDir)
                ->map(fn ($path) => Str::finish(Str::replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR))
                ->implode(PATH_SEPARATOR);

            Process::path($tempDir)
                ->env([
                    'TEXINPUTS' => $texinputs,
                ])
                ->run([
                    'pdflatex',
                    '--max-print-line=10000',
                    '-synctex=1',
                    '-interaction=nonstopmode',
                    '-file-line-error',
                    basename($texFile),
                ])
                ->throw();

            $pdfContent = file_get_contents($pdfFile);

            if ($destination instanceof Closure) {
                return $destination($this, $pdfContent, $fileName, $texFile);
            }

            switch (strtolower($destination)) {
                case 'inline':
                    // Display PDF in browser
                    return response()->file($pdfFile, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    ]);
                case 'download':
                    // Download PDF
                    return response()->download($pdfFile, $fileName, [
                        'Content-Type' => 'application/pdf',
                    ]);
                case 'storage':
                    // Save PDF to storage
                    Storage::put($fileName, $pdfContent);
                    return true;
                case 'storage-inline':
                    // Save PDF to storage and display in browser
                    Storage::put($fileName, $pdfContent);
                    return response()->file($pdfFile, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    ]);
                case 'storage-download':
                    // Save PDF to storage and download
                    Storage::put($fileName, $pdfContent);
                    return response()->download($pdfFile, $fileName, [
                        'Content-Type' => 'application/pdf',
                    ]);
                case 'tex':
                    // Download .tex source
                    $texOutName = pathinfo($fileName, PATHINFO_FILENAME) . '.tex';
                    return response()->download($texFile, $texOutName, [
                        'Content-Type' => 'application/x-tex',
                    ]);
                case 'tex-string':
                    // Return LaTeX source as plain text
                    $texOutName = pathinfo($fileName, PATHINFO_FILENAME) . '.tex';
                    return response($latex, 200, [
                        'Content-Type'        => 'text/plain',
                        'Content-Disposition' => 'inline; filename="' . $texOutName . '"',
                    ]);
                case 'storage-tex':
                    // Save .tex source to storage
                    $texOutName = pathinfo($fileName, PATHINFO_FILENAME) . '.tex';
                    Storage::put($texOutName, file_get_contents($texFile));
                    return true;
                default:
                    throw new InvalidArgumentException("Invalid destination: $destination");
            }
        };
    }
}
