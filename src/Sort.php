<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\IsDefault;
use Honed\Refine\Concerns\HasDirection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends Refiner<TModel, TBuilder>
 */
class Sort extends Refiner
{
    use HasDirection;
    use IsDefault;

    /**
     * Get the value for the sort indicating an ascending direction.
     *
     * @return string
     */
    public function interpretsAscendingValue()
    {
        return $this->getParameter();
    }

    /**
     * Get the value for the sort indicating a descending direction.
     *
     * @return string
     */
    public function getDescendingValue()
    {
        $parameter = $this->getParameter();

        if ($this->isFixed()) {
            return $parameter;
        }

        return \sprintf('-%s', $parameter);
    }

    /**
     * Get the next value to use for the query parameter.
     *
     * @return string|null
     */
    public function getNextDirection()
    {
        $ascending = $this->interpretsAscendingValue();
        $descending = $this->getDescendingValue();

        if ($this->isFixed()) {
            return $this->only === 'desc' ? $ascending : $descending;
        }

        $inverted = $this->isInverted();

        return match (true) {
            $this->isAscending() => $inverted ? null : $descending,
            $this->isDescending() => $inverted ? $ascending : null,
            default => $inverted ? $descending : $ascending,
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return array{string|null, 'asc'|'desc'|null}|null
     */
    public function getValue()
    {
        /** @var array{string|null, 'asc'|'desc'|null}|null */
        return parent::getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('sort');
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        /** @var array{string|null, 'asc'|'desc'|null}|null */
        $value = $this->getValue();

        if (\is_null($value)) {
            return false;
        }

        [$value, $direction] = \array_pad($value, 2, null);

        $active = $value === $this->getParameter();

        if ($this->isFixed()) {
            return $active && $direction === $this->only;
        }

        return $active;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array{string|null, 'asc'|'desc'|null}  $value
     */
    public function getRequestValue($value)
    {
        [$value, $direction] = $value;

        if ($this->isFixed()) {
            $direction = $this->only;
        }

        return [$value, $direction];
    }

    /**
     * {@inheritdoc}
     */
    public function guessParameter()
    {
        $parameter = parent::guessParameter();

        if ($this->isFixed()) {
            $parameter = $parameter.'_'.$this->only;
        }

        return $parameter;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array{string|null, 'asc'|'desc'|null}  $value
     */
    public function getBindings($value)
    {
        [$value, $direction] = $value;

        return \array_merge(parent::getBindings($value), [
            'direction' => $direction,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param  array{string|null, 'asc'|'desc'|null}  $requestValue
     */
    public function refine($builder, $requestValue)
    {
        $applied = parent::refine($builder, $requestValue);

        $value = $this->getValue();

        if ($applied && $value) {
            [$_, $direction] = $value;

            $this->direction($direction);
        }

        return $applied;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return \array_merge(parent::toArray(), [
            'direction' => $this->getDirection(),
            'next' => $this->getNextDirection(),
        ]);
    }

    /**
     * Apply the default sort query scope to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $column
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    public function defaultQuery($builder, $column, $direction)
    {
        $column = $builder->qualifyColumn($column);

        $builder->orderBy($column, $direction ?? 'asc');
    }
}
