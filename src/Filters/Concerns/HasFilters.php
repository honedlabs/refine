<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use Honed\Refine\Filters\Filter;
use Illuminate\Support\Arr;

use function array_filter;
use function array_map;
use function array_values;

trait HasFilters
{
    /**
     * Whether the filters should be applied.
     *
     * @var bool
     */
    protected $filterable = true;

    /**
     * List of the filters.
     *
     * @var array<int,Filter>
     */
    protected $filters = [];

    /**
     * Set whether the filters should be applied.
     *
     * @param  bool  $enable
     * @return $this
     */
    public function filterable($enable = true)
    {
        $this->filterable = $enable;

        return $this;
    }

    /**
     * Determine if the filters should be applied.
     *
     * @return bool
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  Filter|array<int, Filter>  $filters
     * @return $this
     */
    public function filters($filters)
    {
        /** @var array<int, Filter> $filters */
        $filters = is_array($filters) ? $filters : func_get_args();

        $this->filters = [...$this->filters, ...$filters];

        return $this;
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,Filter>
     */
    public function getFilters()
    {
        if (! $this->isFilterable()) {
            return [];
        }

        return once(fn () => array_values(
            array_filter(
                $this->filters,
                static fn (Filter $filter) => $filter->isAllowed()
            )
        ));
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
     * Get the filters as an array for serialization.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtersToArray()
    {
        return array_values(
            array_map(
                static fn (Filter $filter) => $filter->toArray(),
                array_filter(
                    $this->getFilters(),
                    static fn (Filter $filter) => $filter->isVisible()
                )
            )
        );
    }
}
