<?php

declare(strict_types=1);

use Honed\Refine\Stores\Data\SortData;

beforeEach(function () {
    $this->data = new SortData('name', 'asc');
});

it('has array representation', function () {
    expect($this->data)
        ->toArray()->toEqual([
            'col' => 'name',
            'dir' => 'asc',
        ])->jsonSerialize()->toEqual($this->data->toArray());
});

it('validates', function ($input) {
    SortData::from($input);
})->with([
    'string' => ['name'],
    'number' => [1],
    'empty array' => [[]],
    'missing column' => [['dir' => 'asc']],
    'missing direction' => [['col' => 'name']],
    'invalid direction' => [['col' => 'name', 'dir' => 'invalid']],
    'invalid column' => [['col' => 1, 'dir' => 'asc']],
])->throws(InvalidArgumentException::class);

it('passes', function ($input) {
    expect(SortData::from($input))
        ->toBeInstanceOf(SortData::class);
})->with([
    'valid' => [['col' => 'name', 'dir' => 'asc']],
    'null' => [['col' => null, 'dir' => null]],
]);
