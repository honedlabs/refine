<?php

declare(strict_types=1);

use Honed\Refine\FullTextSearch;
use Honed\Refine\Tests\Stubs\Product;

it('has full text search', function () {
    expect(FullTextSearch::make('name'))
        ->toBeInstanceOf(FullTextSearch::class)
        ->isFullText()->toBeTrue();
});

it('applies full text', function () {
    $builder = Product::query();

    expect(FullTextSearch::make('name'))
        ->refine($builder, [true, 'test'])->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->{0}->{'type'}->toBe('Fulltext');
});