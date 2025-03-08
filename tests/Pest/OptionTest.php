<?php

declare(strict_types=1);

use Honed\Refine\Option;

it('can create an option', function () {
    expect(Option::make('test', 'Test'))
        ->toBeInstanceOf(Option::class)
        ->getValue()->toBe('test')
        ->getLabel()->toBe('Test');
});

it('has array representation', function () {
    expect(Option::make('test', 'Test')->toArray())
        ->toBe([
            'value' => 'test',
            'label' => 'Test',
            'active' => false,
        ]);
});