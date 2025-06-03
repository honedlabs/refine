<?php

declare(strict_types=1);

use Honed\Refine\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

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
