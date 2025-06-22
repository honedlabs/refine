<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts\Concerns;

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
     * @param  bool  $enable
     * @return $this
     */
    public function sortable($enable = true)
    {
        $this->sortable = $enable;

        return $this;
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
     * Retrieve the sorts.
     *
     * @return array<int,Sort>
     */
    public function getSorts()
    {
        if (! $this->isSortable()) {
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
        return $this->formatScope($this->sortKey);
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
