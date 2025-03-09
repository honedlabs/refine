<?php

declare(strict_types=1);

use Honed\Refine\Search;
use Honed\Refine\Tests\Stubs\Product;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();
    $this->search = 'search term';
    $this->key = config('refine.key.searches');
});

it('is not active when the params do not match', function () {
    $name = 'name';

    $search = Search::make($name);

    // expect($search->refine($this->builder, ))
});

// it('uses alias over', function () {
//     expect($this->search)
//         ->apply($this->builder, 'test', true, 'and')->toBeTrue()
//         ->isActive()->toBeTrue();

//     expect($this->builder->getQuery()->wheres)->toBeArray()
//         ->toHaveCount(1)
//         ->{0}->scoped(fn ($order) => $order
//         ->{'type'}->toBe('raw')
//         ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
//         ->{'boolean'}->toBe('and')
//         );
// });

// it('changes query boolean', function () {
//     expect($this->search)
//         ->apply($this->builder, 'test', true, 'or')->toBeTrue()
//         ->isActive()->toBeTrue();

//     expect($this->builder->getQuery()->wheres)->toBeArray()
//         ->toHaveCount(1)
//         ->{0}->scoped(fn ($order) => $order
//         ->{'type'}->toBe('raw')
//         ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
//         ->{'boolean'}->toBe('or')
//         );
// });

// it('prevents searching if no value is provided', function () {
//     expect($this->search->apply($this->builder, null, true, 'and'))->toBeFalse();

//     expect($this->builder->getQuery()->wheres)->toBeEmpty();
// });

// it('only executes if it is in array', function () {
//     $columns = [$this->param, 'description'];

//     expect($this->search)
//         ->apply($this->builder, 'test', $columns, 'and')->toBeTrue()
//         ->isActive()->toBeTrue();

//     expect($this->builder->getQuery()->wheres)->toBeArray()
//         ->toHaveCount(1)
//         ->{0}->scoped(fn ($where) => $where
//             ->{'type'}->toBe('raw')
//             ->{'sql'}->toBe("LOWER({$this->builder->qualifyColumn('name')}) LIKE ?")
//             ->{'boolean'}->toBe('and')
//         );
// });
