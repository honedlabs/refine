<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Concerns\Support\SortsKey;
use Honed\Refine\Sorts\Sort;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait HasSorts
{
    use SortsKey;

    /**
     * @var array<int,\Honed\Refine\Sorts\Sort>|null
     */
    protected $sorts;


    /**
     * Merge a set of sorts with the existing sorts.
     * 
     * @param  iterable<\Honed\Refine\Sorts\Sort>  $sorts
     * 
     * @return $this
     */
    public function addSorts(iterable $sorts): static
    {
        if ($sorts instanceof Arrayable) {
            $sorts = $sorts->toArray();
        }

        /**
         * @var array<int, \Honed\Refine\Sorts\Sort> $sorts 
         */
        $this->sorts = \array_merge($this->sorts ?? [], $sorts);

        return $this;
    }

    /**
     * Add a single sort to the list of sorts.
     * 
     * @return $this
     */
    public function addSort(Sort $sort): static
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * Retrieve the sorts.
     * 
     * @return array<int,\Honed\Refine\Sorts\Sort>
     */
    public function getSorts(): array
    {
        return $this->sorts ??= $this->getSourceSorts();
    }

    /**
     * Retrieve the sorts which are available.
     * 
     * @return array<int,\Honed\Refine\Sorts\Sort>
     */
    protected function getSourceSorts(): array
    {
        $sorts = match (true) {
            \method_exists($this, 'sorts') => $this->sorts(),
            default => [],
        };

        return \array_filter(
            $sorts,
            fn (Sort $sort) => $sort->isAllowed()
        );
    }

    /**
     * Determines if the instance has any sorts.
     */
    public function hasSorts(): bool
    {
        return filled($this->getSorts());
    }

    /**
     * Apply a sort to the query.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function sort(Builder $builder, Request $request): static
    {
        $sorts = $this->getSorts();
        $key = $this->getSortsKey();

        $applied = false;

        foreach ($sorts as $sort) {
            $applied |= $sort->apply($builder, $request, $key);
        }

        if (! $applied) {
            $sort = $this->getDefaultSort($sorts);

            $sort?->handle(
                $builder, 
                $sort->getDirection() ?? 'asc', 
                type($sort->getAttribute())->asString()
            );
        }

        return $this;
    }

    /**
     * Find the default sort.
     * 
     * @param  array<int, \Honed\Refine\Sorts\Sort>  $sorts
     */
    public function getDefaultSort(array $sorts): ?Sort
    {
        return Arr::first($sorts, fn (Sort $sort) => $sort->isDefault());
    }

    /**
     * Get the sorts as an array.
     * 
     * @return array<int,mixed>
     */
    public function sortsToArray(): array
    {
        return \array_map(
            static fn (Sort $sort) => $sort->toArray(),
            $this->getSorts()
        );
    }
    
}
