<?php

declare(strict_types=1);

use Honed\Refine\Concerns\HasQualifier;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->test = new class
    {
        use HasQualifier;
    };
});

it('has qualifier', function () {
    expect($this->test)
        // Base case
        ->getQualifier()->toBeTrue()
        ->isQualifying()->toBeTrue()
        // If it does not qualify
        ->qualifies(false)->toBe($this->test)
        ->getQualifier()->toBeFalse()
        ->isQualifying()->toBeFalse()
        // Qualifies with boolean
        ->qualifies()->toBe($this->test)
        ->getQualifier()->toBeTrue()
        ->isQualifying()->toBeTrue()
        // If it has a custom qualifier
        ->on('test')->toBe($this->test)
        ->getQualifier()->toBe('test')
        ->isQualifying()->toBeTrue();
});

it('qualifies column', function () {
    $builder = Product::query();

    expect($this->test)
        // Base case
        ->isQualifying()->toBeTrue()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('products.name')
        // If it does not qualify
        ->qualifies(false)->toBe($this->test)
        ->isQualifying()->toBeFalse()
        ->qualifyColumn('name')->toBe('name')
        ->qualifyColumn('name', $builder)->toBe('name')
        // If it has a cistom qualifier
        ->qualifies('details')->toBe($this->test)
        ->isQualifying()->toBeTrue()
        ->qualifyColumn('name')->toBe('details.name')
        ->qualifyColumn('name', $builder)->toBe('details.name');
});
