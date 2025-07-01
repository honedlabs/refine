<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Workbench\App\Enums\Status;
use Workbench\App\Refiners\RefineProduct;

beforeEach(function () {
    $this->refine = RefineProduct::make();

    $this->parameters = [
        'name' => 'test',
        'price' => 100,
        'status' => \sprintf('%s,%s', Status::Available->value, Status::Unavailable->value),
        'only' => Status::ComingSoon->value,
        'favourite' => '1',
        'oldest' => '2000-01-01',
        'newest' => '2001-01-01',
        $this->refine->getSortKey() => '-price',
        $this->refine->getSearchKey() => 'term',
        $this->refine->getMatchKey() => 'name,description',
    ];

    $this->wheres = [
        [
            'type' => 'raw',
            'sql' => 'LOWER(name) LIKE ?',
            'boolean' => 'and',
        ],
        [
            'type' => 'raw',
            'sql' => 'LOWER(description) LIKE ?',
            'boolean' => 'or',
        ],
        [
            'type' => 'raw',
            'sql' => 'LOWER(name) LIKE ?',
            'boolean' => 'and',
        ],
        [
            'type' => 'Basic',
            'column' => 'price',
            'operator' => '>=',
            'value' => 100,
            'boolean' => 'and',
        ],
        [
            'type' => 'In',
            'column' => 'status',
            'values' => [Status::Available->value, Status::Unavailable->value],
            'boolean' => 'and',
        ],
        [
            'type' => 'In',
            'column' => 'status',
            'values' => [Status::ComingSoon->value],
            'boolean' => 'and',
        ],
        [
            'type' => 'Basic',
            'column' => 'best_seller',
            'operator' => '=',
            'value' => true,
            'boolean' => 'and',
        ],
        [
            'type' => 'Date',
            'column' => 'created_at',
            'boolean' => 'and',
            'operator' => '>=',
            'value' => '2000-01-01',
        ],
        [
            'type' => 'Date',
            'column' => 'created_at',
            'boolean' => 'and',
            'operator' => '<=',
            'value' => '2001-01-01',
        ],
    ];
});

it('has no request', function () {
    expect($this->refine->build())
        ->isNotSorting()->toBeTrue()
        ->isNotSearching()->toBeTrue()
        ->isNotFiltering()->toBeTrue();
});

it('has base pipeline', function () {
    $this->refine
        ->matchable()
        ->request(Request::create('/', Request::METHOD_GET, $this->parameters));

    expect($this->refine->build()->getBuilder()->getQuery())
        ->wheres
        ->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(\count($this->wheres))
            ->toEqualCanonicalizing($this->wheres)
        )->orders->toBeOnlyOrder('price', 'desc');

    expect($this->refine)
        ->isFiltering()->toBeTrue()
        ->isSorting()->toBeTrue()
        ->isSearching()->toBeTrue()
        ->toArray()
        ->scoped(fn ($array) => $array
            ->toHaveKeys([
                '_sort_key',
                '_search_key',
                '_match_key',
                '_delimiter',
                'term',
                'placeholder',
                'sorts',
                'filters',
                'searches',
            ])
            ->{'sorts'}->toBeArray()->toHaveCount(4) // 1 active
            ->{'filters'}->toBeArray()->toHaveCount(7) // 7 active
            ->{'searches'}->toBeArray()->toHaveCount(2) // both active
        );
});

it('has scoped pipeline', function () {
    $this->refine->scope('scope');

    $parameters = [];

    foreach ($this->parameters as $key => $value) {
        Arr::set($parameters, $this->refine->scoped($key), $value);
    }

    $this->refine
        ->request(Request::create('/', Request::METHOD_GET, $parameters));

    expect($this->refine->build()->getBuilder()->getQuery())
        ->wheres
        ->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(\count($this->wheres))
            ->toEqualCanonicalizing($this->wheres)
        )->orders->toBeOnlyOrder('price', 'desc');
});

it('has custom keys pipeline', function () {
    $sort = Arr::pull($this->parameters, 'sort');
    $search = Arr::pull($this->parameters, 'search');
    $match = Arr::pull($this->parameters, 'match');

    Arr::set($this->parameters, 'order', $sort);
    Arr::set($this->parameters, 's', $search);
    Arr::set($this->parameters, 'on', $match);

    $this->refine
        ->sortKey('order')
        ->searchKey('s')
        ->matchKey('on')
        ->request(Request::create('/', Request::METHOD_GET, $this->parameters));

    expect($this->refine->build()->getBuilder()->getQuery())
        ->wheres
        ->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toHaveCount(\count($this->wheres))
            ->toEqualCanonicalizing($this->wheres)
        )->orders->toBeOnlyOrder('price', 'desc');
});
