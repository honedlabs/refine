<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Sort;
use Illuminate\Support\Arr;

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
     * Whether to apply the sorts.
     *
     * @var bool
     */
    protected $sorting = true;

    /**
     * Whether to not provide the sorts.
     *
     * @var bool
     */
    protected $withoutSorts = false;

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  iterable<int, \Honed\Refine\Sort<TModel, TBuilder>>  ...$sorts
     * @return $this
     */
    public function withSorts(...$sorts)
    {
        /** @var array<int, \Honed\Refine\Sort<TModel, TBuilder>> $sorts */
        $sorts = Arr::flatten($sorts);

        $this->sorts = \array_merge($this->sorts ?? [], $sorts);

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
     * Set the instance to apply the sorts.
     *
     * @param  bool  $sorting
     * @return $this
     */
    public function sorting($sorting = true)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Determine if the instance should apply the sorts.
     *
     * @return bool
     */
    public function isSorting()
    {
        return $this->sorting;
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
     * Get the default sort.
     *
     * @return \Honed\Refine\Sort<TModel, TBuilder>|null
     */
    public function getDefaultSort()
    {
        return Arr::first(
            $this->getSorts(),
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
        if ($this->isWithoutSorts()) {
            return [];
        }

        return \array_map(
            static fn (Sort $sort) => $sort->toArray(),
            $this->getSorts()
        );
    }
}
