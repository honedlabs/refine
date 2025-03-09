<?php

declare(strict_types=1);

namespace Honed\Refine;

use BadMethodCallException;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Concerns\InterpretsRequest;
use Honed\Core\Concerns\Validatable;
use Honed\Refine\Concerns\HasDelimiter;
use Honed\Refine\Concerns\HasOptions;
use Honed\Refine\Concerns\HasQueryExpression;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
 */
class Filter extends Refiner
{
    use HasDelimiter;
    use HasOptions {
        multiple as protected setMultiple;
    }
    use HasQueryExpression {
        __call as queryCall;
    }
    use HasScope;
    use InterpretsRequest;
    use Validatable;

    /**
     * The operator to use for the filter.
     *
     * @var string
     */
    protected $operator = '=';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('filter');
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->hasValue();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return \array_merge(parent::toArray(), [
            'value' => $this->getValue(),
            'options' => $this->optionsToArray(),
            'multiple' => $this->isMultiple(),
        ]);
    }

    /**
     * Get the expression partials supported by the filter.
     *
     * @return array<int,string>
     */
    public function expressions()
    {
        return [
            'where',
            'has',
            'withWhere',
        ];
    }

    /**
     * Allow multiple values to be used.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->setMultiple();
        $this->asArray();
        $this->type('select');

        return $this;
    }

    /**
     * Determine if the value is invalid.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function invalidValue($value)
    {
        return ! $this->isActive() ||
            ! $this->validate($value) ||
            ($this->hasOptions() && empty($value));
    }

    /**
     * Get the operator to use for the filter.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set the operator to use for the filter.
     *
     * @param  string  $operator
     * @return $this
     */
    public function operator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Filter the builder using the request.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function refine($builder, $request)
    {
        // We retrieve the parameter according to how the user specified it. As
        // the key is dynamic, we need to be given the scope from the caller to
        // properly interpret the parameter.
        $parameter = $this->getParameter();
        $key = $this->formatScope($parameter);
        $value = $this->interpret($request, $key);

        $this->value($value);

        // If the filter has options, we need to loop over them to set as active
        // if the value is present. This can also override the value, as a
        // `strict` filter will only be active if the value is present in the
        // options array.
        if ($this->hasOptions()) {
            $value = $this->activateOptions($value);
        }

        // The filter may be active, but the value may be invalid. This is done
        // to hide the validation logic from the end-user. It is invalid if it is
        // not active, fails a validation closure, or if the filter has options
        // and the value is empty.
        if ($this->invalidValue($value)) {
            return false;
        }

        // If the filter has a custom query expression, we use it over the default
        // query method. The bindings are passed, but can be overriden with fixed
        // values if needed. In this instance, it is assumed that the developer
        // has called `asBoolean` on the filter and then can write a simple
        // validation closure.
        if ($this->hasQueryExpression()) {
            $bindings = [
                'value' => $value,
                'column' => $this->getName(),
                'table' => $builder->getModel()->getTable(),
            ];

            $this->expressQuery($builder, $bindings);

            return true;
        }

        // If there is no custom query expression, we use the default query
        // method. This depends on how the request interprets the value.
        $column = $this->getName();
        $operator = $this->getOperator();

        $this->apply($builder, $column, $operator, $value);

        return true;
    }

    /**
     * Apply the filter to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string  $column
     * @param  string|null  $operator
     * @param  mixed  $value
     * @return void
     */
    public function apply($builder, $column, $operator, $value)
    {
        $column = $builder->qualifyColumn($column);
        $operator = $operator ?? '=';

        match (true) {
            // If the operator is fuzzy, we do a whereRaw to make it simpler and
            // handle case sensitivity.
            \in_array($operator,
                ['like', 'not like', 'ilike', 'not ilike']
            ) => $builder->whereRaw(
                \sprintf('LOWER(%s) %s ?', $column, \mb_strtoupper($operator, 'UTF8')),
                ['%'.\mb_strtolower(type($value)->asString(), 'UTF8').'%']
            ),

            // The `whereIn` clause should be used if the filter is set to multiple,
            // or if the filter interprets an array. Generally, both should be true
            // as this case is likely when providing options and allowing multiple.
            $this->isMultiple(),
            $this->interpretsArray() => $builder->whereIn($column, $value),

            // If the filter interprets a date, we use whereDate clause as this
            // allows us to compare using the date strings.
            $this->interpretsDate() => $builder->whereDate($column, $operator, $value), // @phpstan-ignore-line

            // This compares a date time string to the column.
            $this->interpretsTime() => $builder->whereTime($column, $operator, $value), // @phpstan-ignore-line

            // Otherwise, we use a standard where clause - but, allow the operator
            // to be overridden by the developer.
            default => $builder->where($column, $operator, $value),
        };

    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array<int,mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Enable macros on the builder, if the call is not to a macro then
        // we assume it is to a method on the builder. We validate this by
        // matching against the expressions.
        try {
            return parent::__call($method, $parameters);
        } catch (BadMethodCallException $e) {
            return $this->queryCall($method, $parameters);
        }
    }
}
