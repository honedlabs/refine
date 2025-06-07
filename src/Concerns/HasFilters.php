<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Filter;
use Illuminate\Support\Arr;

use function array_filter;
use function array_map;
use function array_merge;
use function array_values;

trait HasFilters
{
    /**
     * Whether the filters should be applied.
     *
     * @var bool
     */
    protected $filter = true;

    /**
     * List of the filters.
     *
     * @var array<int,Filter>
     */
    protected $filters = [];

    /**
     * Set whether the filters should be applied.
     *
     * @return $this
     */
    public function disableFiltering()
    {
        $this->filter = false;

        return $this;
    }

    /**
     * Set the filters to not be applied.
     *
     * @return $this
     */
    public function enableFiltering()
    {
        $this->filter = true;

        return $this;
    }

    /**
     * Determine if the filters should be applied.
     *
     * @return bool
     */
    public function filteringEnabled()
    {
        return $this->filter;
    }

    /**
     * Determine if the filters should not be applied.
     *
     * @return bool
     */
    public function filteringDisabled()
    {
        return ! $this->filteringEnabled();
    }

    /**
     * Define the filters for the instance.
     *
     * @return array<int,Filter>
     */
    public function filters()
    {
        return [];
    }

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  Filter|iterable<int, Filter>  ...$filters
     * @return $this
     */
    public function withFilters(...$filters)
    {
        /** @var array<int, Filter> $filters */
        $filters = Arr::flatten($filters);

        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,Filter>
     */
    public function getFilters()
    {
        if ($this->filteringDisabled()) {
            return [];
        }

        return once(fn () => array_values(
            array_filter(
                array_merge($this->filters(), $this->filters),
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
     * Get the filters as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtersToArray()
    {
        return array_map(
            static fn (Filter $filter) => $filter->toArray(),
            $this->getFilters()
        );
    }
}
