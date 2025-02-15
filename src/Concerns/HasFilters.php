<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Filters\Filter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilters
{
    /**
     * List of the filters.
     *
     * @var array<int,\Honed\Refine\Filters\Filter>|null
     */
    protected $filters;

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  iterable<\Honed\Refine\Filters\Filter>  $filters
     * @return $this
     */
    public function addFilters(iterable $filters): static
    {
        if ($filters instanceof Arrayable) {
            $filters = $filters->toArray();
        }

        /**
         * @var array<int, \Honed\Refine\Filters\Filter> $filters
         */
        $this->filters = \array_merge($this->filters ?? [], $filters);

        return $this;
    }

    /**
     * Add a single filter to the list of filters.
     *
     * @return $this
     */
    public function addFilter(Filter $filter): static
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,\Honed\Refine\Filters\Filter>
     */
    public function getFilters(): array
    {
        return $this->filters ??= $this->getSourceFilters();
    }

    /**
     * Retrieve the filters which are available..
     *
     * @return array<int,\Honed\Refine\Filters\Filter>
     */
    protected function getSourceFilters(): array
    {
        $filters = match (true) {
            \method_exists($this, 'filters') => $this->filters(),
            default => [],
        };

        return \array_filter(
            $filters,
            fn (Filter $filter) => $filter->isAllowed()
        );
    }

    /**
     * Determines if the instance has any filters.
     */
    public function hasFilters(): bool
    {
        return filled($this->getFilters());
    }

    /**
     * Apply the filters to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function filter(Builder $builder, Request $request): static
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply($builder, $request);
        }

        return $this;
    }

    /**
     * Get the filters as an array.
     *
     * @return array<int,mixed>
     */
    public function filtersToArray(): array
    {
        return \array_map(
            static fn (Filter $filter) => $filter->toArray(),
            $this->getFilters()
        );
    }
}
