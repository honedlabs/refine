<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

use Honed\Core\Concerns\IsDefault;
use Honed\Refine\Refiner;

class Sort extends Refiner
{
    use IsDefault;

    const ASCENDING = 'asc';

    const DESCENDING = 'desc';

    /**
     * The request direction of the sort.
     *
     * @var 'asc'|'desc'|null
     */
    protected $direction;

    /**
     * Indicate that the sort only acts in a single direction.
     *
     * @var 'asc'|'desc'|null
     */
    protected $only;

    /**
     * Invert the direction for sorts which are not singular.
     *
     * @var bool
     */
    protected $invert = false;

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
    public function getUniqueKey()
    {
        return \sprintf('%s.%s', $this->getAttribute(), $this->getDirection());
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        $isSorting = $this->getValue() === $this->getParameter();

        if ($this->isSingularDirection()) {
            return $isSorting && $this->getDirection() === $this->only;
        }

        return $isSorting;
    }

    /**
     * Apply the request to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return bool
     */
    public function apply($builder, $request, $key)
    {
        [$this->value, $this->direction] = $this->getRefiningValue($request, $key);

        if (! $this->isActive()) {
            return false;
        }

        /** @var string $attribute */
        $attribute = $this->getAttribute();

        $direction = $this->getDirection() ?? 'asc';

        $this->handle($builder, $direction, $attribute);

        return true;
    }

    /**
     * Add the sort query scope to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string  $direction
     * @param  string  $property
     * @return void
     */
    public function handle($builder, $direction, $property)
    {
        $builder->orderBy(
            column: $builder->qualifyColumn($property),
            direction: $direction,
        );
    }

    /**
     * Retrieve the sort value and direction from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return array{0: string|null, 1: 'asc'|'desc'|null}
     */
    public function getRefiningValue($request, $key)
    {
        $sort = $request->safeString($key);

        return match (true) {
            $sort->isEmpty() => [null, null],
            $sort->startsWith('-') => [$sort->after('-')->value(), self::DESCENDING],
            default => [$sort->value(), self::ASCENDING],
        };
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
     * Set the direction to use for the query parameter.
     *
     * @param  'asc'|'desc'|null  $direction
     * @return $this
     */
    public function direction($direction = null)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get the direction to use for the query parameter.
     *
     * @return 'asc'|'desc'|null
     */
    public function getDirection()
    {
        return $this->isSingularDirection() ? $this->only : $this->direction;
    }

    /**
     * Get the next value to use for the query parameter.
     *
     * @return string|null
     */
    public function getNextDirection()
    {
        if ($this->isSingularDirection()) {
            return $this->only === self::DESCENDING
                ? $this->getDescendingValue()
                : $this->getAscendingValue();
        }

        if ($this->isInverted()) {
            return match (true) {
                $this->direction === self::DESCENDING => $this->getAscendingValue(),
                $this->direction === self::ASCENDING => null,
                default => $this->getDescendingValue(),
            };
        }

        return match (true) {
            $this->direction === self::DESCENDING => null,
            $this->direction === self::ASCENDING => $this->getDescendingValue(),
            default => $this->getAscendingValue(),
        };
    }

    /**
     * Get the value for the sort indicating a descending direction.
     *
     * @return string
     */
    public function getDescendingValue()
    {
        return \sprintf('-%s', $this->getParameter());
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
     * Set the sort to be ascending.
     *
     * @return $this
     */
    public function asc()
    {
        $this->only = self::ASCENDING;

        return $this;
    }

    /**
     * Set the sort to be descending.
     *
     * @return $this
     */
    public function desc()
    {
        $this->only = self::DESCENDING;

        return $this;
    }

    /**
     * Determine if the sort only acts in a single direction.
     *
     * @return bool
     */
    public function isSingularDirection()
    {
        return ! \is_null($this->only);
    }

    /**
     * Invert the direction of the sort.
     *
     * @return $this
     */
    public function invert()
    {
        $this->invert = true;

        return $this;
    }

    /**
     * Determine if the sort is inverted.
     *
     * @return bool
     */
    public function isInverted()
    {
        return $this->invert;
    }
}
