<?php

declare(strict_types=1);

namespace Honed\Refine;

use function array_merge;
use function array_pad;
use function is_null;
use function sprintf;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Sort extends Refiner
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'sort';

    /**
     * Whether it is the default.
     *
     * @var bool
     */
    protected $default = false;

    /**
     * The order direction.
     *
     * @var 'asc'|'desc'|null
     */
    protected $direction;

    /**
     * Indicate that the direction is fixed.
     *
     * @var 'asc'|'desc'|null
     */
    protected $fixed;

    /**
     * Whether the direction is inverted.
     *
     * @var bool
     */
    protected $invert = false;

    /**
     * Set as the default.
     *
     * @param  bool  $default
     * @return $this
     */
    public function default($default = true)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Determine if it is the default.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Set the direction.
     *
     * @param  'asc'|'desc'|null  $direction
     * @return $this
     */
    public function direction($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get the direction.
     *
     * @return 'asc'|'desc'|null
     */
    public function getDirection()
    {
        return $this->isFixed() ? $this->fixed : $this->direction;
    }

    /**
     * Determine if the direction is ascending.
     *
     * @return bool
     */
    public function isAscending()
    {
        return $this->direction === 'asc';
    }

    /**
     * Determine if the direction is descending.
     *
     * @return bool
     */
    public function isDescending()
    {
        return $this->direction === 'desc';
    }

    /**
     * Fix the direction to a single value.
     *
     * @param  'asc'|'desc'|null  $direction
     * @return $this
     */
    public function fixed($direction)
    {
        $this->fixed = $direction;

        return $this;
    }

    /**
     * Fix the direction to be ascending.
     *
     * @return $this
     */
    public function asc()
    {
        return $this->fixed('asc');
    }

    /**
     * Fix the direction to be descending.
     *
     * @return $this
     */
    public function desc()
    {
        return $this->fixed('desc');
    }

    /**
     * Determine if the direction is fixed.
     *
     * @return bool
     */
    public function isFixed()
    {
        return isset($this->fixed);
    }

    /**
     * Invert the direction of the sort.
     *
     * @param  bool  $invert
     * @return $this
     */
    public function invert($invert = true)
    {
        $this->invert = $invert;

        return $this;
    }

    /**
     * Determine if the direction is inverted.
     *
     * @return bool
     */
    public function isInverted()
    {
        return $this->invert;
    }

    /**
     * Get the value for the sort indicating an ascending direction.
     *
     * @return string
     */
    public function getAscendingValue()
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

        return sprintf('-%s', $parameter);
    }

    /**
     * Get the next value to use for the query parameter.
     *
     * @return string|null
     */
    public function getNextDirection()
    {
        $ascending = $this->getAscendingValue();
        $descending = $this->getDescendingValue();

        if ($this->isFixed()) {
            return $this->fixed === 'desc'
                ? $ascending
                : $descending;
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
    public function isActive()
    {
        /** @var array{string|null, 'asc'|'desc'|null}|null */
        $value = $this->getValue();

        if (is_null($value)) {
            return false;
        }

        [$value, $direction] = array_pad($value, 2, null);

        $active = $value === $this->getParameter();

        if ($this->isFixed()) {
            return $active && $direction === $this->fixed;
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
            $direction = $this->fixed;
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
            $parameter = $parameter.'_'.$this->fixed;
        }

        return $parameter;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array{string|null, 'asc'|'desc'|null}  $value
     */
    public function getBindings($value, $builder)
    {
        [$value, $direction] = $value;

        return array_merge(parent::getBindings($value, $builder), [
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
    public function toArray($named = [], $typed = [])
    {
        return array_merge(parent::toArray(), [
            'direction' => $this->getDirection(),
            'next' => $this->getNextDirection(),
        ]);
    }

    /**
     *  Apply the default sort query scope to the builder.
     *
     * @param  TBuilder  $builder
     * @param  string  $column
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    public function apply($builder, $column, $direction)
    {
        $builder->orderBy($column, $direction ?? 'asc');
    }
}
