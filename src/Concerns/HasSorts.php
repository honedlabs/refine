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
     */
    protected bool $sortable = true;

    /**
     * List of the sorts.
     *
     * @var array<int,Sort>
     */
    protected array $sorts = [];

    /**
     * The query parameter to identify the sort to apply.
     */
    protected string $sortKey = 'sort';

    /**
     * Set whether the sorts should be applied.
     *
     * @return $this
     */
    public function sortable(bool $value = true): static
    {
        $this->sortable = $value;

        return $this;
    }

    /**
     * Set whether the sorts should not be applied.
     *
     * @return $this
     */
    public function notSortable(bool $value = true): static
    {
        return $this->sortable(! $value);
    }

    /**
     * Determine if the sorts should be applied.
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Determine if the sorts should not be applied.
     */
    public function isNotSortable(): bool
    {
        return ! $this->isSortable();
    }

    /**
     * Merge a set of sorts with the existing sorts.
     *
     * @param  Sort|array<int, Sort>  $sorts
     * @return $this
     */
    public function sorts(Sort|array $sorts): static
    {
        /** @var array<int, Sort> $sorts */
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        $this->sorts = [...$this->sorts, ...$sorts];

        return $this;
    }

    /**
     * Insert a sort.
     *
     * @return $this
     */
    public function sort(Sort $sort): static
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * Retrieve the sorts.
     *
     * @return array<int,Sort>
     */
    public function getSorts(): array
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
     */
    public function getDefaultSort(): ?Sort
    {
        return Arr::first(
            $this->getSorts(),
            static fn (Sort $sort) => $sort->isDefault()
        );
    }

    /**
     * Set the query parameter to identify the sort to apply.
     *
     * @return $this
     */
    public function sortKey(string $sortKey): static
    {
        $this->sortKey = $sortKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the sort to apply.
     */
    public function getSortKey(): string
    {
        return $this->scoped($this->sortKey);
    }

    /**
     * Get the sort being applied.
     */
    public function getActiveSort(): ?Sort
    {
        return Arr::first(
            $this->getSorts(),
            static fn (Sort $sort) => $sort->isActive()
        );
    }

    /**
     * Determine if there is a sort being applied.
     */
    public function isSorting(): bool
    {
        return (bool) $this->getActiveSort();
    }

    /**
     * Determine if there is no sort being applied.
     */
    public function isNotSorting(): bool
    {
        return ! $this->isSorting();
    }

    /**
     * Get the sorts as an array for serialization.
     *
     * @return array<int,array<string,mixed>>
     */
    public function sortsToArray(): array
    {
        return array_values(
            array_map(
                static fn (Sort $sort) => $sort->toArray(),
                array_filter(
                    $this->getSorts(),
                    static fn (Sort $sort) => $sort->isNotHidden()
                )
            )
        );
    }
}
