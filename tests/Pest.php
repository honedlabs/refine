<?php

declare(strict_types=1);

use Honed\Refine\Tests\Stubs\Product;
use Honed\Refine\Tests\Stubs\Status;
use Honed\Refine\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Str;

uses(TestCase::class)->in(__DIR__);

function generate(string $param, mixed $value): Request
{
    return FacadesRequest::create('/', 'GET', [$param => $value]);
}

function product(?string $name = null): Product
{
    return Product::create([
        'public_id' => Str::uuid(),
        'name' => $name ?? fake()->unique()->word(),
        'description' => fake()->sentence(),
        'price' => fake()->randomNumber(4),
        'best_seller' => fake()->boolean(),
        'status' => fake()->randomElement(Status::cases()),
        'created_at' => now()->subDays(fake()->randomNumber(2)),
    ]);
}

expect()->extend('toBeWhere', function (string $column, mixed $value, string $operator = '=', string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveKeys(['type', 'column', 'value', 'operator', 'boolean'])
        ->{'type'}->toBe('Basic')
        ->{'column'}->toBe($column)
        ->{'value'}->toBe($value)
        ->{'operator'}->toBe($operator)
        ->{'boolean'}->toBe($boolean);
});

expect()->extend('toBeOnlyWhere', function (string $column, mixed $value, string $operator = '=', string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($where) => $where
        ->toBeWhere($column, $value, $operator, $boolean)
        );
});

expect()->extend('toBeWhereIn', function (string $column, array $values, string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveKeys(['type', 'column', 'values', 'boolean'])
        ->{'type'}->toBe('In')
        ->{'column'}->toBe($column)
        ->{'values'}->toEqual($values)
        ->{'boolean'}->toBe($boolean);
});

expect()->extend('toBeOnlyWhereIn', function (string $column, array $values, string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($whereIn) => $whereIn
        ->toBeWhereIn($column, $values, $boolean)
        );
});

expect()->extend('toBeSearch', function (string $column, string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveKeys(['type', 'sql', 'boolean'])
        ->{'type'}->toBe('raw')
        ->{'sql'}->toBe(\sprintf('LOWER(%s) LIKE ?', $column))
        ->{'boolean'}->toBe($boolean);
});

expect()->extend('toBeOnlySearch', function (string $column, string $boolean = 'and') {
    return $this->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($search) => $search
        ->toBeSearch($column, $boolean)
        );
});

expect()->extend('toBeOrder', function (string $column, string $direction = 'asc') {
    return $this->toBeArray()
        ->toHaveKeys(['column', 'direction'])
        ->{'column'}->toBe($column)
        ->{'direction'}->toBe($direction);
});

expect()->extend('toBeOnlyOrder', function (string $column, string $direction = 'asc') {
    return $this->toBeArray()
        ->toHaveCount(1)
        ->{0}->scoped(fn ($order) => $order
        ->toBeOrder($column, $direction)
        );
});
