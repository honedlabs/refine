<?php

declare(strict_types=1);

namespace Honed\Refine;

use Closure;
use Honed\Core\Concerns\HasResource;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Parameters;
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
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Throwable;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @mixin TBuilder
 */
class Refine extends Primitive
{
    use ForwardsCalls;
    use HasDelimiter;
    use HasFilters;

    /**
     * @use \Honed\Core\Concerns\HasResource<TModel, TBuilder>
     */
    use HasResource;

    use HasScope;
    use HasSearches {
        getSearchKey as protected baseSearchKey;
        getMatchKey as protected baseMatchKey;
    }
    use HasSorts {
        getSortKey as protected baseSortKey;
    }

    /**
     * The default namespace where refiners reside.
     *
     * @var string
     */
    public static $namespace = 'App\\Refiners\\';

    /**
     * Whether the refine pipeline has been run.
     *
     * @var bool
     */
    protected $refined = false;

    /**
     * Whether the refine pipeline should use Laravel Scout's search.
     *
     * @var bool
     */
    protected $scout = false;

    /**
     * A closure to be called before the refiners have been applied.
     *
     * @var (Closure(TBuilder):void|TBuilder)|null
     */
    protected $before;

    /**
     * A closure to be called after the refiners have been applied.
     *
     * @var (Closure(TBuilder):void|TBuilder)|null
     */
    protected $after;

    /**
     * How to resolve the refiner for the given model name.
     *
     * @var (Closure(class-string<\Illuminate\Database\Eloquent\Model>):class-string<Refine>)|null
     */
    protected static $refinerResolver;

    /**
     * Create a new refine instance.
     */
    public function __construct(
        protected Request $request
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return parent::macroCall($method, $parameters);
        }

        return $this->forwardBuilderCall($method, $parameters);
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
            return $refine->withResource($resource);
        }

        return $refine;
    }

    /**
     * Get a new refiner instance for the given model name.
     *
     * @template TClass of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TClass>  $modelName
     * @param  Closure|null  $before
     * @return Refine<TClass>
     */
    public static function refinerForModel($modelName, $before = null)
    {
        $refiner = static::resolveRefinerName($modelName);

        return $refiner::make($before);
    }

    /**
     * Get the refiner name for the given model name.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     * @return class-string<Refine>
     */
    public static function resolveRefinerName($className)
    {
        $resolver = static::$refinerResolver ?? function (string $className) {
            $appNamespace = static::appNamespace();

            $className = Str::startsWith($className, $appNamespace.'Models\\')
                ? Str::after($className, $appNamespace.'Models\\')
                : Str::after($className, $appNamespace);

            /** @var class-string<Refine> */
            return static::$namespace.$className.'Refiner';
        };

        return $resolver($className);
    }

    /**
     * Specify the default namespace that contains the application's refiners.
     *
     * @param  string  $namespace
     * @return void
     */
    public static function useNamespace($namespace)
    {
        static::$namespace = $namespace;
    }

    /**
     * Specify the callback that should be invoked to guess the name of a refiner for a model.
     *
     * @param  Closure(class-string<\Illuminate\Database\Eloquent\Model>):class-string<Refine>  $callback
     * @return void
     */
    public static function guessRefinersUsing($callback)
    {
        static::$refinerResolver = $callback;
    }

    /**
     * Flush the refine class global configuration statw.
     *
     * @return void
     */
    public static function flushState()
    {
        static::$refinerResolver = null;
        static::$namespace = 'App\\Refine\\';
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
     * @param  (Closure(TBuilder):void|TBuilder)|null  $callback
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
     * @return (Closure(TBuilder):void|TBuilder)|null
     */
    public function getBeforeCallback()
    {
        if (isset($this->before)) {
            return $this->before;
        }

        if ($this instanceof RefinesBefore) {
            return Closure::fromCallable([$this, 'beforeRefining']);
        }

        return null;
    }

    /**
     * Register a callback to be executed after the refiners.
     *
     * @param  (Closure(TBuilder):void|TBuilder)|null  $callback
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
     * @return (Closure(TBuilder):void|TBuilder)|null
     */
    public function getAfterCallback()
    {
        if (isset($this->after)) {
            return $this->after;
        }

        if ($this instanceof RefinesAfter) {
            return Closure::fromCallable([$this, 'afterRefining']);
        }

        return null;
    }

    /**
     * Add the given refiners to be used.
     *
     * @param  array<int, Refiner>|\Illuminate\Support\Collection<int, Refiner>  $refiners
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
     * Get the scoped query parameter to identify the sort.
     *
     * @return string
     */
    public function getSortKey()
    {
        return $this->formatScope($this->baseSortKey());
    }

    /**
     * Get the scoped query parameter to identify the search.
     *
     * @return string
     */
    public function getSearchKey()
    {
        return $this->formatScope($this->baseSearchKey());
    }

    /**
     * Get the scoped query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchKey()
    {
        return $this->formatScope($this->baseMatchKey());
    }

    /**
     * Get the request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the config for the refine as an array.
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
    public function toArray($named = [], $typed = [])
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

        App::make(Pipeline::class)
            ->send($this)
            ->through($this->pipelines())
            ->thenReturn();

        $this->refined = true;

        return $this;
    }

    /**
     * Get the application namespace for the application.
     *
     * @return string
     */
    protected static function appNamespace()
    {
        try {
            return Container::getInstance()
                ->make(Application::class)
                ->getNamespace();
        } catch (Throwable) {
            return 'App\\';
        }
    }

    /**
     * Execute the refiner pipeline.
     *
     * @return array<int, class-string>
     */
    protected function pipelines()
    {
        return [
            BeforeRefining::class,
            RefineSearches::class,
            RefineFilters::class,
            RefineSorts::class,
            AfterRefining::class,
        ];
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

        [$_, $singular, $plural] = Parameters::names($resource);

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
}
