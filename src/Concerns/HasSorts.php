<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Sort;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @phpstan-require-extends \Honed\Core\Primitive
 */
trait HasSorts
{
    /**
     * List of the sorts.
     *
     * @var array<int,\Honed\Refine\Sort<TModel, TBuilder>>
     */
    protected $sorts = [];

    /**
     * The query parameter to identify the sort to apply.
     *
     * @var string|null
     */
    protected $sortKey;

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  \Honed\Refine\Sort<TModel, TBuilder>|iterable<int, \Honed\Refine\Sort<TModel, TBuilder>>  ...$sorts
     * @return $this
     */
    public function sorts(...$sorts)
    {
        /** @var array<int, \Honed\Refine\Sort<TModel, TBuilder>> $sorts */
        $sorts = Arr::flatten($sorts);

        $this->sorts = \array_merge($this->sorts, $sorts);

        return $this;
    }

    /**
     * Define the sorts for the instance.
     *
     * @return array<int,\Honed\Refine\Sort<TModel, TBuilder>>
     */
    public function defineSorts()
    {
        return [];
    }

    /**
     * Retrieve the sorts.
     *
     * @return array<int,\Honed\Refine\Sort<TModel, TBuilder>>
     */
    public function getSorts()
    {
        if (! $this->providesSorts()) {
            return [];
        }

        return once(fn () => \array_values(
            \array_filter(
                \array_merge($this->defineSorts(), $this->sorts),
                static fn (Sort $sort) => $sort->isAllowed()
            )
        ));
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
     * Determine if there is a sort being applied.
     *
     * @return bool
     */
    public function isSorting()
    {
        return (bool) Arr::first(
            $this->getSorts(),
            static fn (Sort $sort) => $sort->isActive()
        );
    }

    /**
     * Set the query parameter to identify the sort to apply.
     *
     * @param  string  $sortKey
     * @return $this
     */
    public function sortKey($sortKey)
    {
        $this->sortKey = $sortKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the sort to apply.
     *
     * @return string
     */
    public function getSortKey()
    {
        return $this->sortKey ?? static::getDefaultSortKey();
    }

    /**
     * Get the default query parameter to identify the sort.
     *
     * @return string
     */
    public static function getDefaultSortKey()
    {
        return type(config('refine.sort_key', 'sort'))->asString();
    }

    /**
     * Set the instance to not provide the sorts.
     *
     * @return $this
     */
    public function exceptSorts()
    {
        return $this->except('sorts');
    }

    /**
     * Set the instance to provide only sorts.
     *
     * @return $this
     */
    public function onlySorts()
    {
        return $this->only('sorts');
    }

    /**
     * Determine if the instance provides the sorts.
     *
     * @return bool
     */
    public function providesSorts()
    {
        return $this->has('sorts');
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
        return \array_map(
            static fn (Sort $sort) => $sort->toArray(),
            $this->getSorts()
        );
    }
}
