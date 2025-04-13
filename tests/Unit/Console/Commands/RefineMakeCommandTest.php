<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::cleanDirectory(app_path('Refiners'));
});

it('makes refines', function () {
    $this->artisan('make:refine', [
        'name' => 'ProductRefine',
    ])->assertSuccessful();

    $this->assertFileExists(app_path('Refiners/ProductRefine.php'));
});

it('prompts for a refine name', function () {
    $this->artisan('make:refine')
        ->expectsQuestion('What should the refine be named?', 'ProductRefine')
        ->assertSuccessful();

    $this->assertFileExists(app_path('Refiners/ProductRefine.php'));
});
