<?php

declare(strict_types=1);

namespace Honed\Refine;

use Closure;
use Honed\Core\Concerns\HasParameterNames;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasDelimiter;
use Honed\Refine\Concerns\HasFilters;
use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Concerns\HasSorts;
use Honed\Refine\Pipelines\AfterRefining;
use Honed\Refine\Pipelines\BeforeRefining;
use Honed\Refine\Pipelines\RefineFilters;
use Honed\Refine\Pipelines\RefineSearches;
use Honed\Refine\Pipelines\RefineSorts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\App;
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

    /** @use HasFilters<TModel, TBuilder> */
    use HasFilters;

    /** @use HasParameterNames<TModel, TBuilder> */
    use HasParameterNames;

    use HasRequest;
    use HasScope;

    /** @use HasSearches<TModel, TBuilder> */
    use HasSearches;

    /** @use HasSorts<TModel, TBuilder> */
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
    public $for;

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
     * Create a new refine instance.
     */
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
        return resolve(static::class)->for(static::createBuilder($query));
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
            $query instanceof Builder, \is_null($query) => $query,

            $query instanceof Model, \class_exists($query) => $query::query(),

            default => throw new \InvalidArgumentException(\sprintf(
                'The provided query [%s] cannot be resolved to a builder instance.',
                $query
            )),
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
     * Get the refiner to be executed before the refiners have been applied.
     *
     * @return \Closure|null
     */
    public function beforeRefiner()
    {
        if (\method_exists($this, 'before')) {
            return Closure::fromCallable([$this, 'before']);
        }

        return $this->before;
    }

    /**
     * Get the refiner to be executed after the refiners have been applied.
     *
     * @return \Closure|null
     */
    public function afterRefiner()
    {
        if (\method_exists($this, 'after')) {
            return Closure::fromCallable([$this, 'after']);
        }

        return $this->after;
    }

    /**
     * Add the given refiners to be used.
     *
     * @param  array<int, \Honed\Refine\Refiner<TModel, TBuilder>>|\Illuminate\Support\Collection<int, \Honed\Refine\Refiner<TModel, TBuilder>>  $refiners
     * @return $this
     */
    public function with($refiners)
    {
        $sorts = [];
        $filters = [];
        $searches = [];

        foreach ($refiners as $refiner) {
            match (true) {
                $refiner instanceof Filter => $filters[] = $refiner,
                $refiner instanceof Sort => $sorts[] = $refiner,
                $refiner instanceof Search => $searches[] = $refiner,
                default => null,
            };
        }

        $this->withSorts($sorts);
        $this->withFilters($filters);
        $this->withSearches($searches);

        return $this;
    }

    /**
     * Set all refiners to not apply.
     *
     * @param  bool  $refining
     * @return $this
     */
    public function refining($refining = true)
    {
        $this->searching($refining);
        $this->filtering($refining);
        $this->sorting($refining);

        return $this;
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
            'matches' => $this->getMatchesKey(),
        ];
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
            'searches' => $this->searchesToArray(),
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

        $this->pipeline();

        $this->refined = true;

        return $this;
    }

    /**
     * Execute the refiner pipeline.
     *
     * @return void
     */
    protected function pipeline()
    {
        App::make(Pipeline::class)
            ->send($this)
            ->through([
                BeforeRefining::class,
                RefineSearches::class,
                RefineFilters::class,
                RefineSorts::class,
                AfterRefining::class,
            ])->thenReturn();
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
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName($parameterName)
    {
        $for = $this->getFor();
        $request = $this->getRequest();

        [$_, $singular, $plural] = static::getParameterNames($for);

        return match ($parameterName) {
            'request' => [$request],
            'route' => [$request->route()],
            'builder' => [$for],
            'query' => [$for],
            $singular => [$for],
            $plural => [$for],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType($parameterType)
    {
        $for = $this->getFor();
        $request = $this->getRequest();

        return match ($parameterType) {
            Request::class => [$request],
            Route::class => [$request->route()],
            Builder::class => [$for],
            default => [App::make($parameterType)],
        };
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
                /** @var array<int, \Honed\Refine\Sort<TModel, TBuilder>> $args */
                $args = $parameters[0];

                return $this->withSorts($args);

            case 'filters':
                /** @var array<int, \Honed\Refine\Filter<TModel, TBuilder>> $args */
                $args = $parameters[0];

                return $this->withFilters($args);

            case 'searches':
                /** @var array<int, \Honed\Refine\Search<TModel, TBuilder>> $args */
                $args = $parameters[0];

                return $this->withSearches($args);

            default:
                return $this->forwardBuilderCall($method, $parameters);
        }
    }
}
