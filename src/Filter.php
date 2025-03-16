<?php

declare(strict_types=1);

namespace Honed\Refine;

use Carbon\Carbon;
use Honed\Core\Concerns\HasMeta;
use Honed\Core\Concerns\HasScope;
use Honed\Core\Concerns\InterpretsRequest;
use Honed\Core\Concerns\Validatable;
use Honed\Refine\Concerns\HasDelimiter;
use Honed\Refine\Concerns\HasOptions;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Refiner<TModel, TBuilder>
 */
class Filter extends Refiner
{
    use HasDelimiter;
    use HasMeta;
    use HasOptions {
        multiple as protected setMultiple;
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
     * Set the filter to be for boolean values.
     *
     * @return $this
     */
    public function boolean()
    {
        $this->type('boolean');
        $this->asBoolean();

        return $this;
    }

    /**
     * Set the filter to be for date values.
     *
     * @return $this
     */
    public function date()
    {
        $this->type('date');
        $this->asDate();

        return $this;
    }

    /**
     * Set the filter to be for date time values.
     *
     * @return $this
     */
    public function dateTime()
    {
        $this->type('datetime');
        $this->asDatetime();

        return $this;
    }

    /**
     * Set the filter to be for float values.
     *
     * @return $this
     */
    public function float()
    {
        $this->type('float');
        $this->asFloat();

        return $this;
    }

    /**
     * Set the filter to be for integer values.
     *
     * @return $this
     */
    public function integer()
    {
        $this->type('integer');
        $this->asInteger();

        return $this;
    }

    /**
     * Set the filter to be for multiple values.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->type('multiple');
        $this->asArray();
        $this->setMultiple();

        return $this;
    }

    /**
     * Set the filter to be for string values.
     *
     * @return $this
     */
    public function string()
    {
        $this->type('string');
        $this->asString();

        return $this;
    }

    /**
     * Set the filter to be for time values.
     *
     * @return $this
     */
    public function time()
    {
        $this->type('time');
        $this->asTime();

        return $this;
    }

    /**
     * Set the operator to use for the filter.
     *
     * @param  string  $operator
     * @return $this
     */
    public function operator($operator)
    {
        $this->operator = \mb_strtoupper($operator, 'UTF8');

        return $this;
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
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('filter');
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Http\Request  $value
     */
    public function getRequestValue($value)
    {
        $parameter = $this->getParameter();

        return $this->interpret($value, $this->formatScope($parameter));
    }

    /**
     * {@inheritdoc}
     */
    public function transformParameter($value)
    {
        if (! $this->hasOptions()) {
            return parent::transformParameter($value);
        }

        return $this->activateOptions($value);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidValue($value)
    {
        return ! $this->validate($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings($value)
    {
        return \array_merge(parent::getBindings($value), [
            'operator' => $this->getOperator(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $value = $this->getValue();

        if ($value instanceof Carbon) {
            $value = $value->toIso8601String();
        }

        return \array_merge(parent::toArray(), [
            'value' => $value,
            'options' => $this->optionsToArray(),
            'multiple' => $this->isMultiple(),
            'meta' => $this->getMeta(),
        ]);
    }

    /**
     * Apply the default filter query to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $column
     * @param  string|null  $operator
     * @param  mixed  $value
     * @return void
     */
    public function defaultQuery($builder, $column, $operator, $value)
    {
        $column = $builder->qualifyColumn($column);

        match (true) {
            \in_array($operator, ['LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE']) => static::queryRaw($builder, $column, type($operator)->asString(), $value),

            $this->isMultiple() || $this->interpretsArray() => $builder->whereIn($column, $value),

            $this->interpretsDate() =>
                // @phpstan-ignore-next-line
                $builder->whereDate($column, $operator, $value),

            $this->interpretsTime() =>
                // @phpstan-ignore-next-line
                $builder->whereTime($column, $operator, $value),

            default => $builder->where($column, $operator, $value),
        };
    }

    /**
     * Query the builder using a raw SQL statement.
     *
     * @param  TBuilder  $builder
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @return void
     */
    protected static function queryRaw($builder, $column, $operator, $value)
    {
        $operator = \mb_strtoupper($operator, 'UTF8');
        $sql = \sprintf('LOWER(%s) %s ?', $column, $operator);
        // @phpstan-ignore-next-line
        $binding = ['%'.\mb_strtolower(\strval($value), 'UTF8').'%'];

        $builder->whereRaw($sql, $binding);
    }
}
