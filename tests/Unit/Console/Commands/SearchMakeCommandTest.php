<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::cleanDirectory(app_path('Searches'));
});

it('makes searches', function () {
    $this->artisan('make:search', [
        'name' => 'NameSearch',
    ])->assertSuccessful();

    $this->assertFileExists(app_path('Searches/NameSearch.php'));
});

it('prompts for a search name', function () {
    $this->artisan('make:search')
        ->expectsQuestion('What should the search be named?', 'NameSearch')
        ->assertSuccessful();

    $this->assertFileExists(app_path('Searches/NameSearch.php'));
});
