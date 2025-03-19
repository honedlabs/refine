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
     * Whether to apply the filters.
     *
     * @var bool
     */
    protected $filtering = true;

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
     * Retrieve the filters.
     *
     * @return array<int,\Honed\Refine\Filter<TModel, TBuilder>>
     */
    public function getFilters()
    {
        return once(function () {

            $filters = \method_exists($this, 'filters') ? $this->filters() : [];

            $filters = \array_merge($filters, $this->filters ?? []);

            return \array_values(
                \array_filter(
                    $filters,
                    static fn (Filter $filter) => $filter->isAllowed()
                )
            );
        });
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
     * Set the instance to apply the filters.
     *
     * @param  bool  $filtering
     * @return $this
     */
    public function filtering($filtering = true)
    {
        $this->filtering = $filtering;

        return $this;
    }

    /**
     * Determine if the instance should apply the filters.
     *
     * @return bool
     */
    public function isFiltering()
    {
        return $this->filtering;
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
        if ($this->isWithoutFilters()) {
            return [];
        }

        return \array_map(
            static fn (Filter $filter) => $filter->toArray(),
            $this->getFilters()
        );
    }
}
