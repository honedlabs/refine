<?php

declare(strict_types=1);

use Honed\Refine\Searches\FullTextSearch;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->search = FullTextSearch::make('name');
});

it('is full text', function () {
    expect($this->search)
        ->isFullText()->toBeTrue();
});

it('applies', function () {
    $builder = Product::query();

    expect($this->search)
        ->handle($builder, 'search', null)->toBeTrue();

    expect($builder->getQuery()->wheres)
        ->{0}->{'type'}->toBe('Fulltext');
});
