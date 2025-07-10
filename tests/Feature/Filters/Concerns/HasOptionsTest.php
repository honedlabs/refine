<?php

declare(strict_types=1);

use Honed\Refine\Filters\Concerns\HasOptions;
use Honed\Refine\Option;
use Workbench\App\Enums\Status;

beforeEach(function () {
    $this->test = new class()
    {
        use HasOptions;
    };
});

it('has options', function () {
    expect($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toBeEmpty()
        )
        ->options([1, 2, 3])->toBe($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toHaveCount(3)
        );
});

it('is strict', function () {
    expect($this->test)
        ->isStrict()->toBeFalse()
        ->strict()->toBe($this->test)
        ->isStrict()->toBeTrue()
        ->lax()->toBe($this->test)
        ->isStrict()->toBeFalse();
});

it('is multiple', function () {
    expect($this->test)
        ->isMultiple()->toBeFalse()
        ->isNotMultiple()->toBeTrue()
        ->multiple()->toBe($this->test)
        ->isMultiple()->toBeTrue()
        ->isNotMultiple()->toBeFalse();
});

it('creates options from backed enum', function () {
    expect($this->test)
        ->options(Status::class)->toBe($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toHaveCount(3)
            ->sequence(
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(Status::Available->value)
                    ->getLabel()->toBe(Status::Available->name)
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(Status::Unavailable->value)
                    ->getLabel()->toBe(Status::Unavailable->name)
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(Status::ComingSoon->value)
                    ->getLabel()->toBe(Status::ComingSoon->name)
                    ->isActive()->toBeFalse(),
            )
        );
});

it('creates options from list', function () {
    expect($this->test)
        ->options([1, 2, 3])->toBe($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toHaveCount(3)
            ->sequence(
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(1)
                    ->getLabel()->toBe('1')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(2)
                    ->getLabel()->toBe('2')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(3)
                    ->getLabel()->toBe('3')
                    ->isActive()->toBeFalse(),
            )
        );
});

it('creates options from associative array', function () {
    expect($this->test)
        ->options([
            1 => 'one',
            2 => 'two',
            3 => 'three',
        ])->toBe($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toHaveCount(3)
            ->sequence(
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(1)
                    ->getLabel()->toBe('one')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(2)
                    ->getLabel()->toBe('two')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(3)
                    ->getLabel()->toBe('three')
                    ->isActive()->toBeFalse(),
            )
        );
});

it('creates options from option array', function () {
    expect($this->test)
        ->options([
            Option::make(1, 'one'),
            Option::make(2, 'two'),
            Option::make(3, 'three'),
        ])->toBe($this->test)
        ->getOptions()
        ->scoped(fn ($test) => $test
            ->toBeArray()
            ->toHaveCount(3)
            ->sequence(
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(1)
                    ->getLabel()->toBe('one')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(2)
                    ->getLabel()->toBe('two')
                    ->isActive()->toBeFalse(),
                fn ($test) => $test
                    ->toBeInstanceOf(Option::class)
                    ->getValue()->toBe(3)
                    ->getLabel()->toBe('three')
                    ->isActive()->toBeFalse(),
            )
        );
});

it('has options from collection', function () {
    expect($this->test)
        ->options(collect([1, 2, 3]))->toBe($this->test)
        ->getOptions()->scoped(fn ($test) => $test
        ->toBeArray()
        ->toHaveCount(3)
        ->sequence(
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(1)
                ->getLabel()->toBe('1')
                ->isActive()->toBeFalse(),
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(2)
                ->getLabel()->toBe('2')
                ->isActive()->toBeFalse(),
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(3)
                ->getLabel()->toBe('3')
                ->isActive()->toBeFalse(),
        )
        );
});

it('activates options', function () {
    $value = Status::Available->value;

    expect($this->test)
        ->options(Status::class)->toBe($this->test)
        ->activateOptions($value)->toBe($value)
        ->getOptions()->scoped(fn ($test) => $test
        ->toBeArray()
        ->toHaveCount(3)
        ->sequence(
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(Status::Available->value)
                ->getLabel()->toBe(Status::Available->name)
                ->isActive()->toBeTrue(),
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(Status::Unavailable->value)
                ->getLabel()->toBe(Status::Unavailable->name)
                ->isActive()->toBeFalse(),
            fn ($test) => $test
                ->toBeInstanceOf(Option::class)
                ->getValue()->toBe(Status::ComingSoon->value)
                ->getLabel()->toBe(Status::ComingSoon->name)
                ->isActive()->toBeFalse(),
        )
        );
});

it('has array representation', function () {
    expect($this->test)
        ->options(Status::class)->toBe($this->test)
        ->optionsToArray()->toBe([
            [
                'value' => Status::Available->value,
                'label' => Status::Available->name,
                'active' => false,
            ],
            [
                'value' => Status::Unavailable->value,
                'label' => Status::Unavailable->name,
                'active' => false,
            ],
            [
                'value' => Status::ComingSoon->value,
                'label' => Status::ComingSoon->name,
                'active' => false,
            ],
        ]);
});
