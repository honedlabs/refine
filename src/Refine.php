<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasBuilderInstance;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasFilters;
use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Concerns\HasSorts;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @mixin TBuilder
 *
 * @extends Primitive<string, mixed>
 */
class Refine extends Primitive
{
    use ForwardsCalls;

    /** @use HasBuilderInstance<TBuilder, TModel> */
    use HasBuilderInstance;

    /** @use HasFilters<TModel> */
    use HasFilters;

    use HasRequest;

    use HasScope;
    /** @use HasSearches<TModel> */
    use HasSearches;
    /** @use HasSorts<TModel> */
    use HasSorts;

    /**
     * Whether the refine pipeline has been run.
     *
     * @var bool
     */
    protected $refined = false;

    /**
     * A closure to be called after the refiners have been applied.
     *
     * @var \Closure|null
     */
    protected $after;

    /**
     * The delimiter to use for array access.
     *
     * @var string|null
     */
    protected $delimiter;

    /**
     * Create a new refine instance.
     */
    public function __construct(Request $request)
    {
        $this->request($request);
    }

    /**
     * Create a new refine instance.
     *
     * @param  TModel|class-string<TModel>|TBuilder  $query
     * @return static
     */
    public static function make($query)
    {
        $query = static::createBuilder($query);

        return resolve(static::class)->builder($query);
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
        return match (true) {
            // @phpstan-ignore-next-line
            $name === 'sorts' => $this->addSorts($arguments[0]),
            // @phpstan-ignore-next-line
            $name === 'filters' => $this->addFilters($arguments[0]),
            // @phpstan-ignore-next-line
            $name === 'searches' => $this->addSearches($arguments[0]),
            // @phpstan-ignore-next-line
            $name === 'after' => $this->after = $arguments[0],
            default => $this->refine()->forwardDecoratedCallTo(
                $this->getBuilder(),
                $name,
                $arguments
            ),
        };
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
     * Set the delimiter to use for array access.
     *
     * @param  string  $delimiter
     * @return $this
     */
    public function delimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get the delimiter to use for array access.
     *
     * @return string|null
     */
    public function getDelimiter()
    {
        if (isset($this->delimiter)) {
            return $this->delimiter;
        }

        return $this->fallbackDelimiter();
    }

    /**
     * Get the fallback delimiter to use for array access.
     *
     * @return string
     */
    public function fallbackDelimiter()
    {
        return type(config('refine.delimiter'))->asString();
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

        $builder = $this->getBuilder();
        $request = $this->getRequest();

        $this->pipeline($builder, $request);

        return $this->markAsRefined();
    }

    /**
     * Execute the refine pipeline.
     *
     * @param  TBuilder<TModel>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function pipeline($builder, $request)
    {
        $this->search($builder, $request);
        $this->filter($builder, $request);
        $this->sort($builder, $request);
        $this->afterRefining($builder, $request);
    }

    /**
     * Execute a closure after refiners have been applied.
     *
     * @param  TBuilder<TModel>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function afterRefining($builder, $request)
    {
        if (isset($this->after)) {
            \call_user_func($this->after, $builder, $request);
        }

        if (\method_exists($this, 'after')) {
            \call_user_func([$this, 'after'], $builder, $request);
        }
    }

    /**
     * Add the given filters or sorts to the refine pipeline.
     *
     * @param  array<int, \Honed\Refine\Refiner>|\Illuminate\Support\Collection<int, \Honed\Refine\Refiner>  $refiners
     * @return $this
     */
    public function using($refiners)
    {
        collect($refiners)->each(function ($refiner) {
            match (true) {
                $refiner instanceof Filter => $this->addFilter($refiner),
                $refiner instanceof Sort => $this->addSort($refiner),
                $refiner instanceof Search => $this->addSearch($refiner),
                default => null,
            };
        });

        return $this;
    }
}
