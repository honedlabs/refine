<?php

declare(strict_types=1);

namespace Honed\Refine;

use Closure;
use Honed\Core\Contracts\HooksIntoLifecycle;
use Honed\Core\Contracts\NullsAsUndefined;
use Honed\Core\Primitive;
use Honed\Persist\Contracts\CanPersistData;
use Honed\Refine\Concerns\CanRefine;
use Illuminate\Container\Container;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
class Refine extends Primitive implements CanPersistData, HooksIntoLifecycle, NullsAsUndefined
{
    use CanRefine;
    use ForwardsCalls;

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'refine';

    /**
     * The default namespace where refiners reside.
     */
    protected static string $namespace = 'App\\Refiners\\';

    /**
     * How to resolve the refiner for the given model name.
     *
     * @var (Closure(class-string<\Illuminate\Database\Eloquent\Model>):class-string<Refine>)|null
     */
    protected static $refinerResolver;

    /**
     * Create a new refine instance.
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request($request);
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

        if ($call = $this->getPersistableCall($method)) {
            return $this->callPersistable($call, $parameters);
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
        return resolve(static::class)
            ->when($resource, fn (Refine $refine, $resource) => $refine->resource($resource));
    }

    /**
     * Get a new refiner instance for the given model name.
     *
     * @template TClass of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TClass>  $modelName
     * @return Refine<TClass>
     */
    public static function refinerForModel(string $modelName): self
    {
        $refiner = static::resolveRefinerName($modelName);

        return $refiner::make($modelName);
    }

    /**
     * Get the refiner name for the given model name.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $className
     * @return class-string<Refine>
     */
    public static function resolveRefinerName(string $className): string
    {
        $resolver = static::$refinerResolver ?? function (string $className) {
            $appNamespace = static::appNamespace();

            $className = Str::startsWith($className, $appNamespace.'Models\\')
                ? Str::after($className, $appNamespace.'Models\\')
                : Str::after($className, $appNamespace);

            /** @var class-string<Refine> */
            return static::$namespace.'Refine'.$className;
        };

        return $resolver($className);
    }

    /**
     * Specify the default namespace that contains the application's refiners.
     */
    public static function useNamespace(string $namespace): void
    {
        static::$namespace = $namespace;
    }

    /**
     * Specify the callback that should be invoked to guess the name of a refiner for a model.
     *
     * @param  Closure(class-string<\Illuminate\Database\Eloquent\Model>):class-string<Refine>  $callback
     */
    public static function guessRefinersUsing(Closure $callback): void
    {
        static::$refinerResolver = $callback;
    }

    /**
     * Flush the global configuration state.
     */
    public static function flushState(): void
    {
        static::$namespace = 'App\\Refiners\\';
        static::$refinerResolver = null;
    }

    public function resolve(): void
    {
        $this->request(App::make(Request::class));
    }

    /**
     * Get the application namespace for the application.
     */
    protected static function appNamespace(): string
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
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        return $this->refineToArray();
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
        return $this->build()
            ->forwardDecoratedCallTo(
                $this->getBuilder(),
                $method,
                $parameters
            );
    }

    /**
     * Provide a selection of default dependencies for evaluation by name.
     *
     * @return array<int, mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'request' => [$this->getRequest()],
            'builder', 'query', 'q' => [$this->getBuilder()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * Provide a selection of default dependencies for evaluation by type.
     *
     * @return array<int, mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $builder = $this->getBuilder();

        return match ($parameterType) {
            self::class => [$this],
            Request::class => [$this->getRequest()],
            $builder::class, Builder::class, BuilderContract::class => [$builder],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
