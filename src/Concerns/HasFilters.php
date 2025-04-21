<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Filter;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @phpstan-require-extends \Honed\Core\Primitive
 */
trait HasFilters
{
    /**
     * List of the filters.
     *
     * @var array<int,\Honed\Refine\Filter<TModel, TBuilder>>
     */
    protected $filters = [];

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  \Honed\Refine\Filter<TModel, TBuilder>|iterable<int, \Honed\Refine\Filter<TModel, TBuilder>>  ...$filters
     * @return $this
     */
    public function filters(...$filters)
    {
        /** @var array<int, \Honed\Refine\Filter<TModel, TBuilder>> $filters */
        $filters = Arr::flatten($filters);

        $this->filters = \array_merge($this->filters, $filters);

        return $this;
    }

    /**
     * Define the filters for the instance.
     *
     * @return array<int,\Honed\Refine\Filter<TModel, TBuilder>>
     */
    public function defineFilters()
    {
        return [];
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,\Honed\Refine\Filter<TModel, TBuilder>>
     */
    public function getFilters()
    {
        if (! $this->providesFilters()) {
            return [];
        }

        return once(fn () => \array_values(
            \array_filter(
                \array_merge($this->defineFilters(), $this->filters),
                static fn (Filter $filter) => $filter->isAllowed()
            )
        ));
    }

    /**
     * Determines if the instance has any filters.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return filled($this->getFilters());
    }

    /**
     * Determine if there is a filter being applied.
     *
     * @return bool
     */
    public function isFiltering()
    {
        return (bool) Arr::first(
            $this->getFilters(),
            static fn (Filter $filter) => $filter->isActive()
        );
    }

    /**
     * Set the instance to not provide the filters.
     *
     * @return $this
     */
    public function exceptFilters()
    {
        return $this->except('filters');
    }

    /**
     * Set the instance to provide only filters.
     *
     * @return $this
     */
    public function onlyFilters()
    {
        return $this->only('filters');
    }

    /**
     * Determine if the instance provides the filters.
     *
     * @return bool
     */
    public function providesFilters()
    {
        return $this->has('filters');
    }

    /**
     * Get the filters as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtersToArray()
    {
        return \array_map(
            static fn (Filter $filter) => $filter->toArray(),
            $this->getFilters()
        );
    }
}
