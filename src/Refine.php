<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasDelimiter;
use Honed\Refine\Concerns\HasFilters;
use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Concerns\HasSorts;
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
    use HasDelimiter;

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

    public function __construct(Request $request)
    {
        $this->request($request);
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
            \class_exists($query) => $query::query(),

            default => throw new \InvalidArgumentException(
                \sprintf(
                    'The provided query [%s] cannot be resolved to a builder instance.',
                    $query
                )
            ),
        };
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
            ? type($this->createBuilder($this->for()))->as(Builder::class)
            : throw new \RuntimeException('Builder instance has not been set.');
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
     * Set all refiners to not apply.
     *
     * @return $this
     */
    public function withoutRefining()
    {
        $this->withoutSearching();
        $this->withoutFiltering();
        $this->withoutSorting();

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
            ...($this->isMatching() ? ['searches' => $this->searchesToArray()] : []),
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
            ...($this->isMatching() ? ['matches' => $this->getMatchesKey()] : []),
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

        $this->refined = true;

        return $this;
    }

    /**
     * Execute the refiner pipeline.
     *
     * @param  TBuilder  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  array<int, \Honed\Refine\Sort>  $sorts
     * @param  array<int, \Honed\Refine\Filter>  $filters
     * @param  array<int, \Honed\Refine\Search>  $searches
     * @return void
     */
    protected function pipeline($builder, $request, $sorts = [], $filters = [], $searches = [])
    {
        $this->beforeRefining($builder, $request);
        $this->search($builder, $request, $searches);
        $this->filter($builder, $request, $filters);
        $this->sort($builder, $request, $sorts);
        $this->afterRefining($builder, $request);
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
     * {@inheritdoc}
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return parent::__call($method, $parameters);
        }

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
                /** @var array<int, \Honed\Refine\Sort> $args */
                $args = $parameters[0];

                return $this->addSorts($args);

            case 'filters':
                /** @var array<int, \Honed\Refine\Filter> $args */
                $args = $parameters[0];

                return $this->addFilters($args);

            case 'searches':
                /** @var array<int, \Honed\Refine\Search> $args */
                $args = $parameters[0];

                return $this->addSearches($args);

            default:
                return $this->forwardBuilderCall($method, $parameters);
        }
    }
}
