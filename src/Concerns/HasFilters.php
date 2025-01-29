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
     * @var array<int,\Honed\Refine\Filters\Filter>|null
     */
    protected $filters;

    /**
     * @param  iterable<\Honed\Refine\Filters\Filter>  $filters
     * @return $this
     */
    public function addFilters(iterable $filters): static
    {
        if ($filters instanceof Arrayable) {
            $filters = $filters->toArray();
        }

        /** @var array<int, \Honed\Refine\Filters\Filter> $filters */
        $this->filters = \array_merge($this->filters ?? [], $filters);

        return $this;
    }

    /**
     * @return $this
     */
    public function addFilter(Filter $filter): static
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return array<int,\Honed\Refine\Filters\Filter>
     */
    public function getFilters(): array
    {
        return $this->filters ??= match (true) {
            \method_exists($this, 'filters') => $this->filters(),
            default => [],
        };
    }

    /**
     * Determines if the instance has any filters.
     */
    public function hasFilters(): bool
    {
        return ! empty($this->getFilters());
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function filter(Builder $builder, Request $request): static
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply($builder, $request);
        }

        return $this;
    }
}
