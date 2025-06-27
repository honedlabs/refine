<?php

declare(strict_types=1);

namespace Honed\Refine\Stores\Data;

use Illuminate\Support\Arr;

class SearchData extends StoreData
{
    /**
     * Create a new sort structure.
     *
     * @param  array<int, string>  $columns
     */
    public function __construct(
        public ?string $term,
        public array $columns,
    ) {}

    /**
     * Attempt to create the structure from a given value.
     *
     * @param  mixed  $value
     * @return self|null
     */
    public static function try($value)
    {
        return match (true) {
            ! is_array($value),
            ! array_key_exists('term', $value),
            ! array_key_exists('cols', $value),
            static::invalidTerm($value['term']),
            static::invalidColumns($value['cols']) => null,
            default => new self($value['term'], $value['cols']) // @phpstan-ignore-line
        };
    }

    /**
     * Get the instance as an array.
     *
     * @return array{term: string|null, cols: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'term' => $this->term,
            'cols' => $this->columns,
        ];
    }

    /**
     * Determine if the term is invalid.
     *
     * @param  mixed  $term
     * @return bool
     */
    protected static function invalidTerm($term)
    {
        return ! is_null($term) && ! is_string($term);
    }

    /**
     * Determine if the column is invalid.
     *
     * @param  mixed  $column
     * @return bool
     */
    protected static function invalidColumns($column)
    {
        return ! is_array($column) || Arr::isAssoc($column);
    }
}
