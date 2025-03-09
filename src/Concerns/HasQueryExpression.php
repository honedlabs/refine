<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Eloquent\Builder;

trait HasQueryExpression
{
    /**
     * The callback or query method to resolve the refiner.
     *
     * @var array<int,mixed>|\Closure|null
     */
    protected $using;

    /**
     * Provide a list of supported expression partials.
     *
     * @return array<int,string>
     */
    abstract public function expressions();

    /**
     * Register the query expression to resolve the refiner.
     *
     * @param  string|\Closure  $statement
     * @param  string|null  $reference
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  bool  $optional
     * @return $this
     *
     * @throws \BadMethodCallException
     */
    public function using($statement, $reference = null, $operator = null, $value = null, $optional = null)
    {
        if ($statement instanceof Closure) {
            $this->using = $statement;

            return $this;
        }

        if ($reference === null) {
            throw new BadMethodCallException(
                'A column or relation reference is required for all expressions.'
            );
        }

        $this->using = func_get_args();

        return $this;
    }

    /**
     * Determine if the refiner has a query expression.
     *
     * @return bool
     */
    public function hasQueryExpression()
    {
        return isset($this->using);
    }

    /**
     * Express the query on the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<string, mixed>  $bindings
     * @return void
     */
    public function expressQuery($builder, $bindings = [])
    {
        /** @var array<string, mixed>|\Closure $using */
        $using = $this->using;

        // If the expression is a direct closure, we evaluate it immediately.
        // We will apply the bindings directly to the closure without rebinding.
        if ($using instanceof Closure) {
            $this->expressClosure($using, $builder, $bindings);

            return;
        }

        // The behaviour of the expression is now dependent on the number of
        // arguments, as it will determine whether the arguments can be closures
        // and whether we need to replace bindings.
        $numArgs = \count($using);

        [$statement, $reference, $operator, $value, $optional] = \array_pad($using, 5, null);

        // If there are only 2 arguments, we have a query method and a column
        // or relation reference. As we know this is a string, we should replace
        // any bindings if they exist. This is applicable to all expressions.
        $reference = $this->replaceBindings($reference, $bindings);
        $operator = $this->replaceBindings($operator, $bindings);
        $value = $this->replaceBindings($value, $bindings);
        $optional = $this->replaceBindings($optional, $bindings);

        if ($numArgs === 2) {
            $builder->{$statement}($reference);

            return;
        }

        // We need to make an assumption about the operator. Some query builder
        // methods (`whereHas`) require this to be a closure. However, this value
        // may also not be a specific value, or operator. If it is a closure, we
        // need to rebind it as it may be requiring a binding injection.
        if ($operator instanceof Closure) {
            $builder->{$statement}($reference, $this->rebindClosure($operator, $bindings));

            return;
        }

        // As it is not a closure, we need to determine whether the operator is
        // an operator or refers to a value. If it is not an operator, then we
        // can assume we have been given a value and we can replace the bindings.
        if ($this->isOperator($operator, $builder)) {
            $builder->{$statement}($reference, $operator, $value);

            return;
        }

        // There is some cases which result in both the reference and operator
        // referring to the column / relation.
        if ($this->isOperator($value, $builder) && $numArgs === 5) {
            $builder->{$statement}($reference, $operator, $value, $optional);

            return;
        }

        $builder->{$statement}($reference, $operator, $value);
    }

    /**
     * Express the callback on the builder.
     *
     * @param  \Closure  $closure
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<string, mixed>  $bindings
     * @return void
     */
    public function expressClosure($closure, $builder, $bindings)
    {
        $model = $builder->getModel();

        $this->evaluate($closure, [
            'builder' => $builder,
            'query' => $builder,
            ...$bindings,
        ], [
            Builder::class => $builder,
            $model::class => $model,
        ]);
    }

    /**
     * Replace the bindings in the reference.
     *
     * @param  mixed  $reference
     * @param  array<string, mixed>  $bindings
     * @return mixed
     */
    public function replaceBindings($reference, $bindings)
    {
        if (! \is_string($reference)) {
            return $reference;
        }

        foreach ($bindings as $key => $value) {
            if ($reference === ':'.$key) {
                return $value;
            }
            // @phpstan-ignore-next-line
            $reference = \str_replace(':'.$key, \strval($value), $reference);
        }

        return $reference;
    }

    /**
     * Rebind a builder closure with the bindings injected to closure arguments.
     *
     * @param  \Closure  $closure
     * @param  array<string, mixed>  $bindings
     * @return \Closure
     */
    public function rebindClosure($closure, $bindings)
    {
        return fn ($builder) => $this->evaluate($closure, [
            'builder' => $builder,
            'query' => $builder,
            ...$bindings,
        ], [
            Builder::class => $builder,
        ]);
    }

    /**
     * Determine if the operator is valid and supported by the query builder.
     *
     * @param  mixed  $operator
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return bool
     */
    public function isOperator($operator, $builder)
    {
        return \in_array($operator, $builder->getQuery()->operators);
    }

    /**
     * Determine if the method is an invalid expression.
     *
     * @param  string  $method
     * @return bool
     */
    public function invalidExpression($method)
    {
        $expressions = $this->expressions();

        if (empty($expressions)) {
            return false;
        }

        // Check that the method starts with any of the given expressions.
        foreach ($expressions as $expression) {
            if (str_starts_with($method, $expression)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle dynamic method calls to the builder.
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     * @return $this
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if ($this->invalidExpression($method)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Call to method %s::%s() is not a supported query expression',
                    static::class, $method
                )
            );
        }

        // @phpstan-ignore-next-line
        return $this->using($method, ...$parameters);
    }
}
