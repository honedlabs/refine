<?php

declare(strict_types=1);

use Honed\Refine\Searches\Search;
use Honed\Refine\Tests\Stubs\Product;

beforeEach(function () {
    $this->builder = Product::query();
    $this->param = 'name';
    $this->search = Search::make($this->param);
    $this->key = config('refine.key.searches');
});

it('searches', function () {
    expect($this->search)
        ->apply($this->builder, 'test', true, 'and')->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
        ->{'boolean'}->toBe('and')
        );
});

it('changes query boolean', function () {
    expect($this->search)
        ->apply($this->builder, 'test', true, 'or')->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
        ->{'boolean'}->toBe('or')
        );
});

it('prevents searching if no value is provided', function () {
    expect($this->search->apply($this->builder, null, true, 'and'))->toBeFalse();

    expect($this->builder->getQuery()->wheres)->toBeEmpty();
});

it('only executes if it is in array', function () {
    $columns = [$this->param, 'description'];

    expect($this->search)
        ->apply($this->builder, 'test', $columns, 'and')->toBeTrue()
        ->isActive()->toBeTrue();

    expect($this->builder->getQuery()->wheres)->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
        ->{'boolean'}->toBe('and')
        );
});
