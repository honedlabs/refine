<?php

declare(strict_types=1);

use Honed\Persist\Exceptions\DriverDataIntegrityException;
use Honed\Refine\Data\SearchData;

beforeEach(function () {
    $this->data = new SearchData('name', ['name', 'description']);
});

it('has array representation', function () {
    expect($this->data)
        ->toArray()->toEqual([
            'term' => 'name',
            'cols' => ['name', 'description'],
        ])->jsonSerialize()->toEqual($this->data->toArray());
});

it('validates', function ($input) {
    SearchData::make($input);
})->with([
    'string' => ['name'],
    'number' => [1],
    'empty array' => [[]],
    'missing term' => [['cols' => ['name', 'description']]],
    'missing columns' => [['term' => 'name']],
])->throws(DriverDataIntegrityException::class);

it('passes', function ($input) {
    expect(SearchData::make($input))
        ->toBeInstanceOf(SearchData::class);
})->with([
    'valid' => [['term' => 'name', 'cols' => ['name', 'description']]],
    'null' => [['term' => null, 'cols' => []]],
]);
