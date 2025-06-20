<?php

declare(strict_types=1);

use Honed\Refine\Concerns\HasQualifier;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->test = new class()
    {
        use HasQualifier;
    };
});

it('has qualifier', function () {
    expect($this->test)
        // Base case
        ->getQualifier()->toBeFalse()
        ->isQualifying()->toBeFalse()
        // If it does not qualify
        ->qualify(false)->toBe($this->test)
        ->getQualifier()->toBeFalse()
        ->isQualifying()->toBeFalse()
        // Qualifies with boolean
        ->qualify()->toBe($this->test)
        ->getQualifier()->toBeTrue()
        ->isQualifying()->toBeTrue();
});

it('isQualifying column', function () {
    $builder = User::query();

    expect($this->test)
        // Base case
        ->isQualifying()->toBeFalse()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('name')
        // If it does not qualify
        ->qualify()->toBe($this->test)
        ->isQualifying()->toBeTrue()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('users.name')
        // If it has a cistom qualifier
        ->qualify('products')->toBe($this->test)
        ->isQualifying()->toBeTrue()
        ->qualifyColumn('name')->toBe('products.name')
        ->qualifyColumn('name', $builder)->toBe('products.name');
});
