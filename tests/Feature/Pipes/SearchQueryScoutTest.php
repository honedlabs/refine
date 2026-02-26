<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;
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

    Product::factory(5)->create([
        'name' => 'test',
    ]);

    Product::factory(5)->create([
        'name' => 'name',
    ]);

    $this->artisan('scout:import', ['model' => Product::class]);
});

it('has scout pipeline', function () {
    expect($this->refine)
        ->scout()->toBe($this->refine)
        ->isScout()->toBeTrue();

    Arr::set($this->parameters, $this->refine->getSearchKey(), 'test');

    $this->refine
        ->request(Request::create('/', Request::METHOD_GET, $this->parameters));

    expect($this->refine->build()->getBuilder()->getQuery())
        ->wheres
        ->scoped(fn ($wheres) => $wheres
            ->toBeArray()
            ->toEqualCanonicalizing([
                [
                    'type' => 'In',
                    'column' => $this->refine->getBuilder()->qualifyColumn('id'),
                    'values' => ['5', '4', '3', '2', '1'],
                    'boolean' => 'and',
                ],
                [
                    'type' => 'raw',
                    'sql' => 'name LIKE ?',
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
            ])
        )->orders->toBeOnlyOrder('price', 'desc');
});
