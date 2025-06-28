<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch('it follows naming conventions')
    ->expect('Agnula\LatexForLaravel')
    ->classes()
    ->toBeFinal();

arch('service providers')
    ->expect('Agnula\LatexForLaravel\LatexForLaravelServiceProvider')
    ->toExtend('Illuminate\Support\ServiceProvider');

arch('compilers extend blade compiler')
    ->expect('Agnula\LatexForLaravel\View\Compilers')
    ->classes()
    ->toExtend('Illuminate\View\Compilers\BladeCompiler');

arch('views namespace follows structure')
    ->expect('Agnula\LatexForLaravel\View')
    ->toOnlyBeUsedIn('Agnula\LatexForLaravel');

arch('mixins have proper method structure')
    ->expect('Agnula\LatexForLaravel\View\ViewMixinLatex')
    ->toHaveMethod('compile');
