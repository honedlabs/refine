<?php

declare(strict_types=1);

namespace Honed\Refine;

use Closure;
use Honed\Core\Concerns\HasParameterNames;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasResource;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Primitive;
use Honed\Refine\Concerns\HasDelimiter;
use Honed\Refine\Concerns\HasFilters;
use Honed\Refine\Concerns\HasSearches;
use Honed\Refine\Concerns\HasSorts;
use Honed\Refine\Contracts\RefinesAfter;
use Honed\Refine\Contracts\RefinesBefore;
use Honed\Refine\Pipelines\AfterRefining;
use Honed\Refine\Pipelines\BeforeRefining;
use Honed\Refine\Pipelines\RefineFilters;
use Honed\Refine\Pipelines\RefineSearches;
use Honed\Refine\Pipelines\RefineSorts;
use Illuminate\Database\Eloquent\Builder;
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
 */
class Refine extends Primitive
{
    use ForwardsCalls;

    use HasDelimiter;

    /** @use \Honed\Refine\Concerns\HasFilters<TModel, TBuilder> */
    use HasFilters;

    /** @use \Honed\Core\Concerns\HasParameterNames<TModel, TBuilder> */
    use HasParameterNames;

    use HasRequest;

    /**
     * @use \Honed\Core\Concerns\HasResource<TModel, TBuilder>
     */
    use HasResource;
    use HasScope;

    /** @use \Honed\Refine\Concerns\HasSearches<TModel, TBuilder> */
    use HasSearches {
        getSearchKey as protected getBaseSearchKey;
        getMatchKey as protected getBaseMatchKey;
    }

    /** @use \Honed\Refine\Concerns\HasSorts<TModel, TBuilder> */
    use HasSorts {
        getSortKey as protected getBaseSortKey;
    }

    /**
     * Whether the refine pipeline has been run.
     *
     * @var bool
     */
    protected $refined = false;

    /**
     * A closure to be called before the refiners have been applied.
     *
     * @var \Closure(TBuilder):void|null
     */
    protected $before;

    /**
     * A closure to be called after the refiners have been applied.
     *
     * @var \Closure(TBuilder):void|null
     */
    protected $after;

    /**
     * Create a new refine instance.
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request($request);
    }

    /**
     * Create a new refine instance.
     *
     * @param  TModel|class-string<TModel>|TBuilder|null  $resource
     * @return static
     */
    public static function make($resource = null)
    {
        $refine = resolve(static::class);

        if ($resource) {
            return $refine->resource($resource);
        }

        return $refine;
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
     * Register a callback to be executed before the refiners.
     *
     * @param  \Closure(TBuilder):void  $callback
     * @return $this
     */
    public function before($callback)
    {
        $this->before = $callback;

        return $this;
    }

    /**
     * Get the refiner to be executed before the refiners have been applied.
     *
     * @return \Closure(TBuilder):void|null
     */
    public function getBeforeCallback()
    {
        if (isset($this->before)) {
            return $this->before;
        }

        if ($this instanceof RefinesBefore) {
            return \Closure::fromCallable([$this, 'beforeRefining']);
        }

        return null;
    }

    /**
     * Register a callback to be executed after the refiners.
     *
     * @param  \Closure(TBuilder):void  $callback
     * @return $this
     */
    public function after($callback)
    {
        $this->after = $callback;

        return $this;
    }

    /**
     * Get the refiner to be executed after the refiners have been applied.
     *
     * @return \Closure(TBuilder):void|null
     */
    public function getAfterCallback()
    {
        if (isset($this->after)) {
            return $this->after;
        }

        if ($this instanceof RefinesAfter) {
            return \Closure::fromCallable([$this, 'afterRefining']);
        }

        return null;
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

        $this->sorts($sorts);
        $this->filters($filters);
        $this->searches($searches);

        return $this;
    }

    /**
     * Determine if the instance provides refinements.
     *
     * @return bool
     */
    public function isRefinable()
    {
        return $this->hasAny('filters', 'searches', 'sorts');
    }

    /**
     * Determine if the instance does not provide any refinements.
     *
     * @return bool
     */
    public function isntRefinable()
    {
        return ! $this->isRefinable();
    }

    /**
     * Set the instance to not provide any refinements.
     *
     * @return $this
     */
    public function exceptRefinements()
    {
        $this->except('filters', 'searches', 'sorts');

        return $this;
    }

    /**
     * Set the instance to only provide refinements.
     *
     * @return $this
     */
    public function onlyRefinements()
    {
        $this->only('filters', 'searches', 'sorts');

        return $this;

    }

    /**
     * Get the scoped query parameter to identify the sort.
     *
     * @return string
     */
    public function getSortKey()
    {
        return $this->formatScope($this->getBaseSortKey());
    }

    /**
     * Get the scoped query parameter to identify the search.
     *
     * @return string
     */
    public function getSearchKey()
    {
        return $this->formatScope($this->getBaseSearchKey());
    }

    /**
     * Get the scoped query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchKey()
    {
        return $this->formatScope($this->getBaseMatchKey());
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
            'term' => $this->getTerm(),
            'sort' => $this->getSortKey(),
            'search' => $this->getSearchKey(),
            'match' => $this->getMatchKey(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'config' => $this->configToArray(),
            'sorts' => $this->sortsToArray(),
            'filters' => $this->filtersToArray(),
            'searches' => $this->searchesToArray(),
        ];
    }

    /**
     * Refine the resource using the provided refinements.
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
     * Forward a call to the resource.
     *
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    protected function forwardBuilderCall($method, $parameters)
    {
        return $this->refine()
            ->forwardDecoratedCallTo(
                $this->getResource(),
                $method,
                $parameters
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName($parameterName)
    {
        $resource = $this->getResource();
        $request = $this->getRequest();

        [$_, $singular, $plural] = static::getParameterNames($resource);

        return match ($parameterName) {
            'request' => [$request],
            'route' => [$request->route()],
            'builder',
            'resource',
            'query',
            $singular,
            $plural => [$resource],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType($parameterType)
    {
        $resource = $this->getResource();
        $request = $this->getRequest();

        return match ($parameterType) {
            Request::class => [$request],
            Route::class => [$request->route()],
            Builder::class => [$resource],
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

        return $this->forwardBuilderCall($method, $parameters);
    }
}
