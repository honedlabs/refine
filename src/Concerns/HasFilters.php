<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Filters\Filter;

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
    protected array $filters = [];

    /**
     * Set whether the filters should be applied.
     *
     * @return $this
     */
    public function filterable(bool $value = true): static
    {
        $this->filterable = $value;

        return $this;
    }

    /**
     * Set whether the filters should not be applied.
     *
     * @return $this
     */
    public function notFilterable(bool $value = true): static
    {
        return $this->filterable(! $value);
    }

    /**
     * Determine if the filters should be applied.
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * Determine if the filters should not be applied.
     */
    public function isNotFilterable(): bool
    {
        return ! $this->isFilterable();
    }

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  Filter|array<int, Filter>  $filters
     * @return $this
     */
    public function filters(Filter|array $filters): static
    {
        /** @var array<int, Filter> $filters */
        $filters = is_array($filters) ? $filters : func_get_args();

        $this->filters = [...$this->filters, ...$filters];

        return $this;
    }

    /**
     * Insert a filter.
     *
     * @return $this
     */
    public function filter(Filter $filter): static
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,Filter>
     */
    public function getFilters(): array
    {
        if ($this->isNotFilterable()) {
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
     */
    public function isFiltering(): bool
    {
        return (bool) $this->getActiveFilters();
    }

    /**
     * Determine if there is no filter being applied.
     */
    public function isNotFiltering(): bool
    {
        return ! $this->isFiltering();
    }

    /**
     * Get the filters being applied.
     *
     * @return array<int,Filter>
     */
    public function getActiveFilters(): array
    {
        return array_values(
            array_filter(
                $this->getFilters(),
                static fn (Filter $filter) => $filter->isActive()
            )
        );
    }

    /**
     * Get the filters as an array for serialization.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtersToArray(): array
    {
        return array_values(
            array_map(
                static fn (Filter $filter) => $filter->toArray(),
                array_filter(
                    $this->getFilters(),
                    static fn (Filter $filter) => $filter->isNotHidden()
                )
            )
        );
    }
}
