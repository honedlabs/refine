<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Sorts\Sort;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasSorts
{
    const SortKey = 'sort';

    /**
     * @var array<int,\Honed\Refine\Sorts\Sort>|null
     */
    protected $sorts;

    protected string $sortKey = self::SortKey;

    /**
     * @param  iterable<\Honed\Refine\Sorts\Sort>  $sorts
     * @return $this
     */
    public function addSorts(iterable $sorts): static
    {
        if ($sorts instanceof Arrayable) {
            $sorts = $sorts->toArray();
        }

        /** @var array<int, \Honed\Refine\Sorts\Sort> $sorts */
        $this->sorts = \array_merge($this->sorts ?? [], $sorts);

        return $this;
    }

    /**
     * @return $this
     */
    public function addSort(Sort $sort): static
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * @return array<int,\Honed\Refine\Sorts\Sort>
     */
    public function getSorts(): array
    {
        return $this->sorts ??= match (true) {
            \method_exists($this, 'sorts') => $this->sorts(),
            default => [],
        };
    }

    /**
     * Determines if the instance has any sorts.
     */
    public function hasSorts(): bool
    {
        return ! empty($this->getSorts());
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function sort(Builder $builder, Request $request): static
    {
        $sorts = $this->getSorts();

        $applied = false;

        foreach ($sorts as $sort) {
            $applied |= $sort->apply($builder, $request, $this->getSortKey());
        }

        if (! $applied) {
            /** @var \Honed\Refine\Sorts\Sort|null $sort */
            $sort = \array_find($sorts, static fn (Sort $sort) => $sort->isDefault());

            $sort?->handle($builder, $sort->getDirection() ?? 'asc', type($sort->getAttribute())->asString());
        }

        return $this;
    }

    /**
     * Sets the sort key to look for in the request.
     */
    public function sortKey(string $sortKey): static
    {
        $this->sortKey = $sortKey;

        return $this;
    }

    /**
     * Gets the sort key to look for in the request.
     */
    public function getSortKey(): string
    {
        return $this->sortKey;
    }
}
