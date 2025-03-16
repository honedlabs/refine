<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Interpreter;
use Honed\Refine\Sort;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 */
trait HasSorts
{
    /**
     * List of the sorts.
     *
     * @var array<int,\Honed\Refine\Sort<TModel, TBuilder>>|null
     */
    protected $sorts;

    /**
     * The query parameter to identify the sort to apply.
     *
     * @var string|null
     */
    protected $sortsKey;

    /**
     * Whether to not apply the sorts.
     *
     * @var bool
     */
    protected $withoutSorting = false;

    /**
     * Whether to not provide the sorts.
     *
     * @var bool
     */
    protected $withoutSorts = false;

    /**
     * Format a value using the scope.
     *
     * @param  string  $value
     * @return string
     */
    abstract public function formatScope($value);

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  array<int, \Honed\Refine\Sort<TModel, TBuilder>>|\Illuminate\Support\Collection<int, \Honed\Refine\Sort<TModel, TBuilder>>  $sorts
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
     * @param  \Honed\Refine\Sort<TModel, TBuilder>  $sort
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
     * @return array<int,\Honed\Refine\Sort<TModel, TBuilder>>
     */
    public function getSorts()
    {
        return once(function () {

            $sorts = \method_exists($this, 'sorts') ? $this->sorts() : [];

            $sorts = \array_merge($sorts, $this->sorts ?? []);

            return \array_values(
                \array_filter(
                    $sorts,
                    static fn (Sort $sort) => $sort->isAllowed()
                )
            );
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
        return $this->sortsKey ?? static::fallbackSortsKey();
    }

    /**
     * Get the query parameter to identify the sort to apply from the config.
     *
     * @return string
     */
    public static function fallbackSortsKey()
    {
        return type(config('refine.sorts_key', 'sort'))->asString();
    }

    /**
     * Set the instance to not apply the sorts.
     *
     * @param  bool  $withoutSorting
     * @return $this
     */
    public function withoutSorting($withoutSorting = true)
    {
        $this->withoutSorting = $withoutSorting;

        return $this;
    }

    /**
     * Determine if the instance should not apply the sorts.
     *
     * @return bool
     */
    public function isWithoutSorting()
    {
        return $this->withoutSorting;
    }

    /**
     * Set the instance to not provide the sorts.
     *
     * @param  bool  $withoutSorts
     * @return $this
     */
    public function withoutSorts($withoutSorts = true)
    {
        $this->withoutSorts = $withoutSorts;

        return $this;
    }

    /**
     * Determine if the instance should not provide the sorts.
     *
     * @return bool
     */
    public function isWithoutSorts()
    {
        return $this->withoutSorts;
    }

    /**
     * Retrieve the sort value and direction from a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getSortAndDirection($request)
    {
        $key = $this->formatScope($this->getSortsKey());

        $sort = Interpreter::interpretStringable($request, $key);

        if (\is_null($sort) || $sort->isEmpty()) {
            return [null, null];
        }

        if ($sort->startsWith('-')) {
            return [$sort->after('-')->value(), 'desc'];
        }

        return [$sort->value(), 'asc'];
    }

    /**
     * Apply a default sort to the query.
     *
     * @param  TBuilder  $builder
     * @param  array<int, \Honed\Refine\Sort<TModel, TBuilder>>  $sorts
     * @return void
     */
    public function sortDefault($builder, $sorts)
    {
        $sort = Arr::first(
            $sorts,
            static fn (Sort $sort) => $sort->isDefault()
        );

        if (! $sort) {
            return;
        }

        $column = $sort->getName();
        $direction = $sort->getDirection() ?? 'asc';

        $sort->defaultQuery($builder, $column, $direction);
    }

    /**
     * Get the sorts as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function sortsToArray()
    {
        if ($this->isWithoutSorts()) {
            return [];
        }

        return \array_map(
            static fn (Sort $sort) => $sort->toArray(),
            $this->getSorts()
        );
    }

    /**
     * Apply the sort to the query.
     *
     * @param  TBuilder  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  array<int, \Honed\Refine\Sort<TModel, TBuilder>>  $sorts
     * @return $this
     */
    public function sort($builder, $request, $sorts = [])
    {
        if ($this->isWithoutSorting()) {
            return $this;
        }

        $value = $this->getSortAndDirection($request);

        /** @var array<int, \Honed\Refine\Sort<TModel, TBuilder>> */
        $sorts = \array_merge($this->getSorts(), $sorts);

        $applied = false;

        foreach ($sorts as $sort) {
            $applied |= $sort->refine($builder, $value);
        }

        if (! $applied) {
            $this->sortDefault($builder, $sorts);
        }

        return $this;
    }
}
