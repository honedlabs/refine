<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Sorts\Sort;
use Illuminate\Support\Arr;

use function array_filter;
use function array_map;
use function array_values;

trait HasSorts
{
    /**
     * Whether the sorts should be applied.
     *
     * @var bool
     */
    protected $sortable = true;

    /**
     * List of the sorts.
     *
     * @var array<int,Sort>
     */
    protected $sorts = [];

    /**
     * The query parameter to identify the sort to apply.
     *
     * @var string
     */
    protected $sortKey = 'sort';

    /**
     * Set whether the sorts should be applied.
     *
     * @param  bool  $value
     * @return $this
     */
    public function sortable($value = true)
    {
        $this->sortable = $value;

        return $this;
    }

    /**
     * Set whether the sorts should not be applied.
     *
     * @param  bool  $value
     * @return $this
     */
    public function notSortable($value = true)
    {
        return $this->sortable(! $value);
    }

    /**
     * Determine if the sorts should be applied.
     *
     * @return bool
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Determine if the sorts should not be applied.
     *
     * @return bool
     */
    public function isNotSortable()
    {
        return ! $this->isSortable();
    }

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  Sort|array<int, Sort>  $sorts
     * @return $this
     */
    public function sorts($sorts)
    {
        /** @var array<int, Sort> $sorts */
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        $this->sorts = [...$this->sorts, ...$sorts];

        return $this;
    }

    /**
     * Insert a sort.
     *
     * @param  Sort  $sort
     * @return $this
     */
    public function sort($sort)
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * Retrieve the sorts.
     *
     * @return array<int,Sort>
     */
    public function getSorts()
    {
        if ($this->isNotSortable()) {
            return [];
        }

        return once(fn () => array_values(
            array_filter(
                $this->sorts,
                static fn (Sort $sort) => $sort->isAllowed()
            )
        ));
    }

    /**
     * Get the default sort.
     *
     * @return Sort|null
     */
    public function getDefaultSort()
    {
        return Arr::first(
            $this->getSorts(),
            static fn (Sort $sort) => $sort->isDefault()
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
        return $this->scoped($this->sortKey);
    }

    /**
     * Get the sort being applied.
     *
     * @return Sort|null
     */
    public function getActiveSort()
    {
        return Arr::first(
            $this->getSorts(),
            static fn (Sort $sort) => $sort->isActive()
        );
    }

    /**
     * Determine if there is a sort being applied.
     *
     * @return bool
     */
    public function isSorting()
    {
        return (bool) $this->getActiveSort();
    }

    /**
     * Determine if there is no sort being applied.
     *
     * @return bool
     */
    public function isNotSorting()
    {
        return ! $this->isSorting();
    }

    /**
     * Get the sorts as an array for serialization.
     *
     * @return array<int,array<string,mixed>>
     */
    public function sortsToArray()
    {
        return array_values(
            array_map(
                static fn (Sort $sort) => $sort->toArray(),
                array_filter(
                    $this->getSorts(),
                    static fn (Sort $sort) => $sort->isVisible()
                )
            )
        );
    }
}
