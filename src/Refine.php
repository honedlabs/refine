<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
 *
 * @extends Primitive<string, mixed>
 */
class Refine extends Primitive
{
    use Concerns\HasBuilderInstance;
    use Concerns\HasFilters;
    use Concerns\HasRequest;
    use Concerns\HasSearch;
    use Concerns\HasSorts;
    use ForwardsCalls;
    use HasScope;

    protected bool $refined = false;

    // protected bool $scout = false;

    public function __construct(Request $request)
    {
        $this->request($request);
    }

    /**
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     */
    public function __call($name, $arguments): mixed
    {
        if ($name === 'sorts') {
            /** @var array<int, \Honed\Refine\Sorts\Sort> $arguments */
            return $this->addSorts($arguments);
        }

        if ($name === 'filters') {
            /** @var array<int, \Honed\Refine\Filters\Filter> $arguments */
            return $this->addFilters($arguments);
        }

        // Delay the refine call
        $this->refine();

        return $this->forwardDecoratedCallTo($this->getBuilder(), $name, $arguments);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $model
     */
    public static function make(Model|string|Builder $model): static
    {
        return static::query($model);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    public static function model(Model|string $model): static
    {
        return static::query($model);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public static function query(Model|string|Builder $query): static
    {
        $query = static::createBuilder($query);

        return resolve(static::class)->builder($query);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray()
    {
        return [
            'sorts' => $this->getSorts(),
            'filters' => $this->getFilters(),
            ...($this->hasMatches() ? ['searches' => $this->getSearches()] : []),
            'search' => [
                'value' => $this->getSearchValue(),
            ],
            'keys' => [
                'sorts' => $this->getSortKey(),
                'search' => $this->getSearchKey(),
                ...($this->hasMatches() ? ['match' => $this->getMatchKey()] : []),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function refinements(): array
    {
        return $this->toArray();
    }

    /**
     * Refine the builder using the provided refinements.
     *
     * @return $this
     */
    public function refine(): static
    {
        if ($this->isRefined()) {
            return $this;
        }

        $builder = $this->getBuilder();

        $request = $this->getRequest();

        $this->search($builder, $request);
        $this->sort($builder, $request);
        $this->filter($builder, $request);

        $this->refined = true;

        return $this;
    }

    /**
     * Add the given filters or sorts to the refine pipeline.
     *
     * @param  iterable<\Honed\Refine\Refiner>  $refiners
     * @return $this
     */
    public function with(iterable $refiners): static
    {
        foreach ($refiners as $refiner) {
            match (true) {
                $refiner instanceof Filter => $this->addFilter($refiner),
                $refiner instanceof Sort => $this->addSort($refiner),
                $refiner instanceof Search => $this->addSearch($refiner),
                default => null,
            };
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function for(Request $request): static
    {
        return $this->request($request);
    }

    /**
     * Determine if the refine pipeline has been run.
     */
    public function isRefined(): bool
    {
        return $this->refined;
    }
}
