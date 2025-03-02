<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Pest;

use Honed\Refine\Refine;
use Honed\Refine\Sorts\Sort;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function () {
    $this->refine = Refine::make(Product::class);
});


it('has for method', function () {
    expect($this->refine)
        ->for(Product::class)->toBe($this->refine)
        ->getFor()->toBeInstanceOf(Builder::class);
});

it('has before method', function () {
    expect($this->refine)
        ->before(function () {
            return $this->refine;
        })->toBe($this->refine);
});

it('has after method', function () {
    expect($this->refine)
        ->after(function () {
            return $this->refine;
        })->toBe($this->refine);
});

it('calls sorts', function () {
    expect($this->refine)
        ->sorts([Sort::make('name', 'A-Z')])->toBe($this->refine)
        ->getSorts()->toHaveCount(1);
});

it('has filters method', function () {
    expect($this->refine)
        ->filters([Filter::make('name')])->toBe($this->refine)
        ->getFilters()->toHaveCount(1);
});

it('has searches method', function () {
    expect($this->refine)
        ->searches([Search::make('name')])->toBe($this->refine)
        ->getSearches()->toHaveCount(1);
});
