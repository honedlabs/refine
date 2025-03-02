<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasFilters;
use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Concerns\HasSorts;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Searches\Search;
use Honed\Refine\Sorts\Sort;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
     * The builder instance to refine.
     *
     * @var TBuilder|null
     */
    protected $for;

    /**
     * A closure to be called before the refiners have been applied.
     *
     * @var \Closure|null
     */
    protected $before;

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

    public function __construct(Request $request)
    {
        $this->request($request);
    }

    /**
     * {@inheritdoc}
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        switch ($method) {
            case 'for':
                /** @var TModel|class-string<TModel>|TBuilder $for */
                $for = $parameters[0];
                $this->for = static::createBuilder($for);

                return $this;

            case 'before':
                /** @var \Closure|null $before */
                $before = $parameters[0];
                $this->before = $before;

                return $this;

            case 'after':
                /** @var \Closure|null $after */
                $after = $parameters[0];
                $this->after = $after;

                return $this;

            case 'sorts':
                /** @var array<int, \Honed\Refine\Sorts\Sort> $args */
                $args = $parameters[0];

                return $this->addSorts($args);

            case 'filters':
                /** @var array<int, \Honed\Refine\Filters\Filter> $args */
                $args = $parameters[0];

                return $this->addFilters($args);

            case 'searches':
                /** @var array<int, \Honed\Refine\Searches\Search> $args */
                $args = $parameters[0];

                return $this->addSearches($args);

            default:
                return $this->forwardBuilderCall($method, $parameters);
        }
    }

    /**
     * Create a new refine instance.
     *
     * @param  TModel|class-string<TModel>|TBuilder|null  $query
     * @return static
     */
    public static function make($query = null)
    {
        return resolve(static::class)
            ->for(static::createBuilder($query));
    }

    /**
     * Create a new builder instance.
     *
     * @param  TModel|class-string<TModel>|TBuilder|null  $query
     * @return TBuilder|null
     *
     * @throws \InvalidArgumentException
     */
    public static function createBuilder($query)
    {
        /** @var TBuilder|null */
        return match (true) {
            $query instanceof Builder,
            \is_null($query) => $query,

            $query instanceof Model,
            static::isModelClassString($query) => $query::query(),

            default => static::throwInvalidBuilderException($query),
        };
    }

    /**
     * Determine if the query is a model class string.
     *
     * @param  mixed  $query
     * @return bool
     */
    public static function isModelClassString($query)
    {
        return \is_string($query)
            && \class_exists($query)
            && \is_subclass_of($query, Model::class);
    }

    /**
     * Forward a call to the builder.
     *
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    protected function forwardBuilderCall($method, $parameters)
    {
        return $this->refine()
            ->forwardDecoratedCallTo(
                $this->getFor(),
                $method,
                $parameters
            );
    }

    /**
     * Get the builder the refiner is for.
     *
     * @return TBuilder
     *
     * @throws \RuntimeException
     */
    public function getFor()
    {
        return $this->for ??= \method_exists($this, 'for') 
            ? $this->for() 
            : static::throwMissingBuilderException();
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
     * Execute a closure before refiners have been applied.
     *
     * @param  TBuilder<TModel>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeRefining($builder, $request)
    {
        if (isset($this->before)) {
            \call_user_func($this->before, $builder, $request);
        }

        if (\method_exists($this, 'before')) {
            \call_user_func([$this, 'before'], $builder, $request);
        }
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

        $for = $this->getFor();
        $request = $this->getRequest();

        $this->pipeline($for, $request);

        return $this->markAsRefined();
    }

    /**
     * Execute the refiner pipeline.
     *
     * @param  TBuilder  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function pipeline($builder, $request)
    {
        $this->beforeRefining($builder, $request);
        $this->search($builder, $request);
        $this->filter($builder, $request);
        $this->sort($builder, $request);
        $this->afterRefining($builder, $request);
    }

    /**
     * Throw an exception if the builder instance has not been set when called.
     *
     * @return never
     *
     * @throws \RuntimeException
     */
    protected static function throwMissingBuilderException()
    {
        throw new \RuntimeException('Builder instance has not been set.');
    }

    /**
     * Throw an exception if the argument cannot be resolved to a builder instance.
     *
     * @param  string  $query
     * @return never
     *
     * @throws \InvalidArgumentException
     */
    protected static function throwInvalidBuilderException($query)
    {
        throw new \InvalidArgumentException(
            \sprintf(
                'The provided query [%s] cannot be resolved to a builder instance.',
                $query
            )
        );
    }
}
