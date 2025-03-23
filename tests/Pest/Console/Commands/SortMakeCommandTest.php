<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::cleanDirectory(app_path('Refiners'));
});

it('makes sorts', function () {
    $this->artisan('make:sort', [
        'name' => 'DateSort',
    ])->assertSuccessful();

    $this->assertFileExists(app_path('Refiners/Sorts/DateSort.php'));
});

it('prompts for a sort name', function () {
    $this->artisan('make:sort')
        ->expectsQuestion('What should the sort be named?', 'DateSort')
        ->assertSuccessful();

    $this->assertFileExists(app_path('Refiners/Sorts/DateSort.php'));
});