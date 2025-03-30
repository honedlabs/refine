<?php

declare(strict_types=1);

use Honed\Refine\Tests\Fixtures\RefineFixture;
use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->builder = Product::query();

    $this->term = 'search term';

    $this->request = Request::create('/', 'GET', [
        'name' => 'test',
        'price' => 100,
        'status' => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value),
        'only' => Status::ComingSoon->value,
        'favourite' => '1',
        'oldest' => '2000-01-01',
        'newest' => '2001-01-01',
        config('refine.sort_key') => '-price',
        config('refine.search_key') => $this->term,
    ]);
});

it('executes pipeline', function () {
    $refine = RefineFixture::make(Product::query())
        ->request($this->request)
        ->refine();

    expect($refine->getBuilder()->getQuery())
        ->wheres->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(9)
            ->toEqualCanonicalizing([
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                    'boolean' => 'and',
                ],
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('description')}) LIKE ?",
                    'boolean' => 'or',
                ],
                [
                    'type' => 'raw',
                    'sql' => "LOWER({$this->builder->qualifyColumn('name')}) LIKE ?",
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Basic',
                    'column' => $this->builder->qualifyColumn('price'),
                    'operator' => '>=',
                    'value' => 100,
                    'boolean' => 'and',
                ],
                [
                    'type' => 'In',
                    'column' => $this->builder->qualifyColumn('status'),
                    'values' => [Status::Available->value, Status::Unavailable->value],
                    'boolean' => 'and',
                ],
                [
                    'type' => 'In',
                    'column' => $this->builder->qualifyColumn('status'),
                    'values' => [Status::ComingSoon->value],
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Basic',
                    'column' => $this->builder->qualifyColumn('best_seller'),
                    'operator' => '=',
                    'value' => true,
                    'boolean' => 'and',
                ],
                [
                    'type' => 'Date',
                    'column' => $this->builder->qualifyColumn('created_at'),
                    'boolean' => 'and',
                    'operator' => '>=',
                    'value' => '2000-01-01',
                ],
                [
                    'type' => 'Date',
                    'column' => $this->builder->qualifyColumn('created_at'),
                    'boolean' => 'and',
                    'operator' => '<=',
                    'value' => '2001-01-01',
                ],
            ])
        )->orders->toBeOnlyOrder($this->builder->qualifyColumn('price'), 'desc');
});