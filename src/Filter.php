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
use Honed\Refine\Concerns\HasSearch;

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
        multiple as protected baseMultiple;
    }
    use HasScope;

    /**
     * @use HasSearch<TModel, TBuilder>
     */
    use HasSearch;

    use InterpretsRequest;
    use Validatable;

    /**
     * The operator to use for the filter.
     *
     * @var string
     */
    protected $operator = '=';

    /**
     * Whether the filter only responds to presence values.
     *
     * @var bool
     */
    protected $presence = false;

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
    public function datetime()
    {
        $this->type('datetime');
        $this->asDatetime();

        return $this;
    }

    /**
     * Set the filter to use options from an enum.
     *
     * @param  class-string<\BackedEnum>  $enum
     * @param  bool  $multiple
     * @return $this
     */
    public function enum($enum, $multiple = false)
    {
        $this->options($enum);

        /** @var 'int'|'string'|null $backing */
        $backing = (new \ReflectionEnum($enum))
            ->getBackingType()
            ?->getName();

        $this->subtype($backing);
        $this->multiple($multiple);

        return $this;
    }

    /**
     * Set the filter to be for float values.
     *
     * @return $this
     */
    public function float()
    {
        $this->type('number');
        $this->asFloat();

        return $this;
    }

    /**
     * Set the filter to be for integer values.
     *
     * @return $this
     */
    public function int()
    {
        $this->type('number');
        $this->asInt();

        return $this;
    }

    /**
     * Set the filter to be for multiple values.
     *
     * @param  bool  $multiple
     * @return $this
     */
    public function multiple($multiple = true)
    {
        $this->type('multiple');
        $this->asArray();
        $this->baseMultiple($multiple);

        return $this;
    }

    /**
     * Set the filter to be for text values.
     *
     * @return $this
     */
    public function text()
    {
        $this->type('text');
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
     * Set the operator to be '>'
     *
     * @return $this
     */
    public function gt()
    {
        return $this->operator('>');
    }

    /**
     * Set the operator to be '>='
     *
     * @return $this
     */
    public function gte()
    {
        return $this->operator('>=');
    }

    /**
     * Set the operator to be '<'
     *
     * @return $this
     */
    public function lt()
    {
        return $this->operator('<');
    }

    /**
     * Set the operator to be '<='
     *
     * @return $this
     */
    public function lte()
    {
        return $this->operator('<=');
    }

    /**
     * Set the operator to be '!='
     *
     * @return $this
     */
    public function neq()
    {
        return $this->operator('!=');
    }

    /**
     * Set the operator to be '='
     *
     * @return $this
     */
    public function eq()
    {
        return $this->operator('=');
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
     * Set the filter to respond only to presence values.
     *
     * @return $this
     */
    public function presence()
    {
        $this->boolean();
        $this->presence = true;

        return $this;
    }

    /**
     * Determine if the filter only responds to presence values.
     *
     * @return bool
     */
    public function isPresence()
    {
        return $this->presence;
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
        if ($this->hasOptions()) {
            return $this->activateOptions($value);
        }

        if ($this->isPresence()) {
            return $value ?: null;
        }

        return $value;
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
        if ($this->isQualifying()) {
            $column = $builder->qualifyColumn($column);
        }

        match (true) {
            $this->isFullText() && \is_string($value) => $this->searchRecall(
                $builder,
                $value,
                $column
            ),

            \in_array($operator, ['LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE']) &&
                \is_string($value) => $this->searchPrecision(
                    $builder,
                    $value,
                    $column,
                    // @phpstan-ignore-next-line
                    operator: $operator
                ),

            $this->isMultiple() ||
                $this->interpretsArray() => $builder->whereIn($column, $value),

            $this->interpretsDate() =>
                // @phpstan-ignore-next-line
                $builder->whereDate($column, $operator, $value),

            $this->interpretsTime() =>
                // @phpstan-ignore-next-line
                $builder->whereTime($column, $operator, $value),

            default => $builder->where($column, $operator, $value),
        };
    }
}
