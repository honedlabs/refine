<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Concerns\HasRequest;
use Honed\Refine\Filters\Filter;
use Illuminate\Support\Collection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
trait HasFilters
{
    use HasRequest;

    /**
     * List of the filters.
     *
     * @var array<int,\Honed\Refine\Filters\Filter>|null
     */
    protected $filters;

    /**
     * Merge a set of filters with the existing filters.
     *
     * @param  array<int, \Honed\Refine\Filters\Filter>|\Illuminate\Support\Collection<int, \Honed\Refine\Filters\Filter>  $filters
     * @return $this
     */
    public function addFilters($filters)
    {
        if ($filters instanceof Collection) {
            $filters = $filters->all();
        }

        $this->filters = \array_merge($this->filters ?? [], $filters);

        return $this;
    }

    /**
     * Add a single filter to the list of filters.
     *
     * @param  \Honed\Refine\Filters\Filter  $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Retrieve the filters.
     *
     * @return array<int,\Honed\Refine\Filters\Filter>
     */
    public function getFilters()
    {
        return once(function () {
            $methodFilters = method_exists($this, 'filters') ? $this->filters() : [];
            $propertyFilters = $this->filters ?? [];

            return collect($propertyFilters)
                ->merge($methodFilters)
                ->filter(static fn (Filter $filter) => $filter->isAllowed())
                ->unique(static fn (Filter $filter) => $filter->getUniqueKey())
                ->values()
                ->all();
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
     * Apply the filters to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function filter($builder, $request)
    {
        $filters = $this->getFilters();

        foreach ($filters as $filter) {
            $filter->scope($this->getScope())->apply($builder, $request);
        }

        return $this;
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
