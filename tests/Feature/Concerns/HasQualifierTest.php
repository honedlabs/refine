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

afterEach(function () {
    $this->test::shouldQualify(false);
});

it('has qualifier', function () {
    expect($this->test)
        // Base case
        ->getQualifier()->toBeFalse()
        ->qualifies()->toBeFalse()
        // If it does not qualify
        ->qualify(false)->toBe($this->test)
        ->getQualifier()->toBeFalse()
        ->qualifies()->toBeFalse()
        // Qualifies with boolean
        ->qualify()->toBe($this->test)
        ->getQualifier()->toBeTrue()
        ->qualifies()->toBeTrue();
});

it('has qualifier globally', function () {
    $this->test::shouldQualify(true);

    expect($this->test)
        ->getQualifier()->toBeTrue()
        ->qualifies()->toBeTrue();
});

it('qualifies column', function () {
    $builder = User::query();

    expect($this->test)
        // Base case
        ->qualifies()->toBeFalse()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('name')
        // If it does not qualify
        ->qualify()->toBe($this->test)
        ->qualifies()->toBeTrue()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('users.name')
        // If it has a cistom qualifier
        ->qualify('products')->toBe($this->test)
        ->qualifies()->toBeTrue()
        ->qualifyColumn('name')->toBe('products.name')
        ->qualifyColumn('name', $builder)->toBe('products.name');
});
