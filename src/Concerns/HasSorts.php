<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Sorts\Sort;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasSorts
{
    use AccessesRequest;

    /**
     * The query parameter to identify the sort to apply.
     *
     * @var string|null
     */
    protected $sortsKey;

    /**
     * List of the sorts.
     *
     * @var array<int,\Honed\Refine\Sorts\Sort>|null
     */
    protected $sorts;

    /**
     * Set the query parameter to identify the sort to apply.
     *
     * @param  string  $sortsKey
     * @return $this
     */
    public function sortsKey($sortsKey)
    {
        $this->sortsKey = $sortsKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the sort to apply.
     *
     * @return string
     */
    public function getSortsKey()
    {
        if (isset($this->sortsKey)) {
            return $this->sortsKey;
        }

        return $this->getFallbackSortsKey();
    }

    /**
     * Get the fallback query parameter to identify the sort to apply.
     *
     * @return string
     */
    protected function getFallbackSortsKey()
    {
        return type(config('refine.config.sorts', 'sort'))->asString();
    }

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  array<int, \Honed\Refine\Sorts\Sort>|\Illuminate\Support\Collection<int, \Honed\Refine\Sorts\Sort>  $sorts
     * @return $this
     */
    public function addSorts($sorts)
    {
        if ($sorts instanceof Collection) {
            $sorts = $sorts->all();
        }

        $this->sorts = \array_merge($this->sorts ?? [], $sorts);

        return $this;
    }

    /**
     * Add a single sort to the list of sorts.
     *
     * @param  \Honed\Refine\Sorts\Sort  $sort
     * @return $this
     */
    public function addSort($sort)
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * Retrieve the sorts.
     *
     * @return array<int,\Honed\Refine\Sorts\Sort>
     */
    public function getSorts()
    {
        return once(function () {
            $methodSorts = method_exists($this, 'sorts') ? $this->sorts() : [];
            $propertySorts = $this->sorts ?? [];

            return collect($propertySorts)
                ->merge($methodSorts)
                ->filter(static fn (Sort $sort) => $sort->isAllowed())
                ->unique(static fn (Sort $sort) => $sort->getUniqueKey())
                ->values()
                ->all();
        });
    }

    /**
     * Determines if the instance has any sorts.
     *
     * @return bool
     */
    public function hasSorts()
    {
        return filled($this->getSorts());
    }

    /**
     * Apply a sort to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function sort($builder)
    {
        $sorts = $this->getSorts();
        $key = $this->formatScope($this->getSortsKey());
        $request = $this->getRequest();

        $applied = false;

        foreach ($sorts as $sort) {
            $applied |= $sort->apply($builder, $request, $key);
        }

        if (! $applied) {
            $this->sortByDefault($builder, $sorts);
        }

        return $this;
    }

    /**
     * Apply a default sort to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<int, \Honed\Refine\Sorts\Sort>  $sorts
     * @return void
     */
    protected function sortByDefault($builder, $sorts)
    {
        $sort = $this->getDefaultSort($sorts);

        $sort?->handle(
            $builder,
            $sort->getDirection() ?? 'asc',
            type($sort->getAttribute())->asString()
        );
    }

    /**
     * Find the default sort.
     *
     * @param  array<int, \Honed\Refine\Sorts\Sort>  $sorts
     * @return \Honed\Refine\Sorts\Sort|null
     */
    protected function getDefaultSort($sorts)
    {
        return Arr::first(
            $sorts,
            static fn (Sort $sort) => $sort->isDefault()
        );
    }

    /**
     * Get the sorts as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function sortsToArray()
    {
        return \array_map(
            static fn (Sort $sort) => $sort->toArray(),
            $this->getSorts()
        );
    }
}
