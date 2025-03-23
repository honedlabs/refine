<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Filter;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
trait HasFilters
{
    /**
     * List of the filters.
     *
     * @var array<int,\Honed\Refine\Filter<TModel, TBuilder>>|null
     */
    protected $filters;

    /**
     * Whether to provide the filters.
     *
     * @var bool
     */
    protected $withoutFilters = false;

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  iterable<int, \Honed\Refine\Filter<TModel, TBuilder>>  ...$filters
     * @return $this
     */
    public function withFilters(...$filters)
    {
        /** @var array<int, \Honed\Refine\Filter<TModel, TBuilder>> $filters */
        $filters = Arr::flatten($filters);

        $this->filters = \array_merge($this->filters ?? [], $filters);

        return $this;
    }

    /**
     * Define the filters for the instance.
     *
     * @return array<int,\Honed\Refine\Filter<TModel, TBuilder>>
     */
    public function filters()
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
        if ($this->isWithoutFilters()) {
            return [];
        }

        return once(fn () => \array_values(
            \array_filter(
                \array_merge($this->filters(), $this->filters ?? []),
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
     * Set the instance to not provide the filters.
     *
     * @param  bool  $withoutFilters
     * @return $this
     */
    public function withoutFilters($withoutFilters = true)
    {
        $this->withoutFilters = $withoutFilters;

        return $this;
    }

    /**
     * Determine if the instance should not provide the filters when serializing.
     *
     * @return bool
     */
    public function isWithoutFilters()
    {
        return $this->withoutFilters;
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
