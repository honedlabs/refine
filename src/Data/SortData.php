<?php

declare(strict_types=1);

namespace Honed\Refine\Data;

use Honed\Persist\PersistData;

class SortData extends PersistData
{
    /**
     * Create a new sort structure.
     *
     * @param  'asc'|'desc'|null  $direction
     */
    public function __construct(
        public ?string $column,
        public ?string $direction,
    ) {}

    /**
     * Attempt to create the structure from a given value.
     */
    public static function from(mixed $value): ?static
    {
        /** @var static|null */
        return match (true) {
            ! is_array($value),
            ! array_key_exists('col', $value),
            ! array_key_exists('dir', $value),
            static::invalidColumn($value['col']),
            static::invalidDirection($value['dir']) => null,
            default => new self($value['col'], $value['dir']), // @phpstan-ignore-line
        };
    }

    /**
     * Get the instance as an array.
     *
     * @return array{col: string|null, dir: 'asc'|'desc'|null}
     */
    public function toArray(): array
    {
        return [
            'col' => $this->column,
            'dir' => $this->direction,
        ];
    }

    /**
     * Determine if the column is invalid.
     *
     * @param  mixed  $column
     * @return bool
     */
    protected static function invalidColumn($column)
    {
        return ! is_null($column) && ! is_string($column);
    }

    /**
     * Determine if the direction is invalid.
     *
     * @param  mixed  $direction
     * @return bool
     */
    protected static function invalidDirection($direction)
    {
        return ! in_array($direction, ['asc', 'desc', null]);
    }
}
