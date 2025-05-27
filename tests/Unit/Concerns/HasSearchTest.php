<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Pest\Concerns;

use Honed\Refine\Concerns\HasSearch;
use Honed\Refine\Tests\Stubs\Product;

use function sprintf;

beforeEach(function () {
    $this->builder = Product::query();

    $this->test = new class()
    {
        use HasSearch;
    };
});

it('is full text', function () {
    expect($this->test)
        ->isFullText()->toBeFalse()
        ->fullText()->toBe($this->test)
        ->isFullText()->toBeTrue();
});

it('precision search', function () {
    $this->test->searchPrecision($this->builder, 'test', 'name');

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->toEqual([
            [
                'type' => 'raw',
                'sql' => sprintf('LOWER(%s) LIKE ?', 'name'),
                'boolean' => 'and',
            ],
        ]);
});

it('recall search', function () {
    $this->test->searchRecall($this->builder, 'test', 'name');

    expect($this->builder->getQuery()->wheres)
        ->toBeArray()
        ->toHaveCount(1)
        ->toEqual([
            [
                'type' => 'Fulltext',
                'columns' => [
                    'name',
                ],
                'options' => [],
                'value' => 'test',
                'boolean' => 'and',
            ],
        ]);
});
