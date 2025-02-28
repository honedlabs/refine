<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasBuilderInstance;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\AccessesRequest;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Primitive<string, mixed>
 */
class Refine extends Primitive
{
    use AccessesRequest;
    use Concerns\HasFilters;
    use Concerns\HasSearches;
    use Concerns\HasSorts;
    use ForwardsCalls;
    use HasBuilderInstance;

    /**
     * Whether the refine pipeline has been run.
     *
     * @var bool
     */
    protected $refined = false;

    /**
     * Create a new refine instance.
     */
    public function __construct(Request $request)
    {
        $this->request($request);
    }

    /**
     * Mark the refine pipeline as refined.
     *
     * @return $this
     */
    protected function markAsRefined()
    {
        $this->refined = true;

        return $this;
    }

    /**
     * Determine if the refine pipeline has been run.
     *
     * @return bool
     */
    public function isRefined()
    {
        return $this->refined;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $name
     * @param  array<int, mixed>  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {

        if ($name === 'sorts') {
            /** @var array<int, \Honed\Refine\Sorts\Sort> $argument */
            $argument = $arguments[0];

            return $this->addSorts($argument);
        }

        if ($name === 'filters') {
            /** @var array<int, \Honed\Refine\Filters\Filter> $argument */
            $argument = $arguments[0];

            return $this->addFilters($argument);
        }

        if ($name === 'searches') {
            /** @var array<int, \Honed\Refine\Searches\Search> $argument */
            $argument = $arguments[0];

            return $this->addSearches($argument);
        }

        // Delay the refine call until records are retrieved
        return $this->refine()->forwardDecoratedCallTo(
            $this->getBuilder(),
            $name,
            $arguments
        );
    }

    /**
     * Create a new refine instance.
     *
     * @param  TModel|class-string<TModel>|\Illuminate\Database\Eloquent\Builder<TModel>  $query
     * @return static
     */
    public static function make($query)
    {
        $query = static::createBuilder($query);

        return resolve(static::class)->builder($query);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'sorts' => $this->sortsToArray(),
            'filters' => $this->filtersToArray(),
            'config' => $this->configToArray(),
            ...($this->canMatch() ? ['searches' => $this->searchesToArray()] : []),
        ];
    }

    /**
     * Get the config for the refiner as an array.
     *
     * @return array<string,mixed>
     */
    public function configToArray()
    {
        return [
            'delimiter' => $this->getDelimiter(),
            'search' => $this->getTerm(),
            'searches' => $this->getSearchesKey(),
            'sorts' => $this->getSortsKey(),
            ...($this->canMatch() ? ['matches' => $this->getMatchesKey()] : []),
        ];
    }

    /**
     * Refine the builder using the provided refinements.
     *
     * @return $this
     */
    public function refine()
    {
        if ($this->isRefined()) {
            return $this;
        }

        $this->pipe([
            'search',
            'sort',
            'filter',
        ]);

        return $this->markAsRefined();
    }

    /**
     * Pipe the builder through a series of methods.
     *
     * @param  array<int,string>  $pipes
     * @return $this
     */
    public function pipe($pipes)
    {
        $builder = $this->getBuilder();

        foreach ($pipes as $pipe) {
            $this->{$pipe}($builder);
        }

        return $this;
    }

    /**
     * Add the given filters or sorts to the refine pipeline.
     *
     * @param  array<int, \Honed\Refine\Refiner>|\Illuminate\Support\Collection<int, \Honed\Refine\Refiner>  $refiners
     * @return $this
     */
    public function using($refiners)
    {
        if ($refiners instanceof Collection) {
            $refiners = $refiners->all();
        }

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
}
