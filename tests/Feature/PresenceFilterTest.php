<?php

declare(strict_types=1);

use Honed\Refine\PresenceFilter;
use Illuminate\Support\Facades\Request;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->builder = Product::query();

    $this->filter = PresenceFilter::make('status')
        ->query(fn ($query) => $query->where('status', Status::Available->value));
});

it('has presence filter', function () {
    expect($this->filter)
        ->getType()->toBe('boolean')
        ->interpretsAs()->toBe('boolean')
        ->isPresence()->toBeTrue();
});

it('does not apply', function () {
    $request = Request::create('/', 'GET', ['status' => '0']);

    expect($this->filter)
        ->refine($this->builder, $request)->toBeFalse();

    expect($this->builder->getQuery()->wheres)
        ->toBeEmpty();
});

it('applies', function () {
    $request = Request::create('/', 'GET', ['status' => '1']);

    expect($this->filter)
        ->refine($this->builder, $request)->toBeTrue();

    expect($this->builder->getQuery()->wheres)
        ->toBeOnlyWhere('status', Status::Available->value);
});
