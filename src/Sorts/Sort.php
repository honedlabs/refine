<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

use Honed\Core\Concerns\IsDefault;
use Honed\Refine\Refiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Sort extends Refiner
{
    use IsDefault;

    /**
     * @var 'asc'|'desc'|null
     */
    protected $direction;

    /**
     * @var 'asc'|'desc'|null
     */
    protected $only;

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
        return $this->getValue() === $this->getParameter();
    }

    /**
     * Apply the request to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $sortKey
     * @return bool
     */
    public function apply($builder, $request, $sortKey)
    {
        [$this->value, $this->direction] = $this->getValueFromRequest($request, $sortKey);

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
     * @param  string  $sortKey
     * @return array{0: string|null, 1: 'asc'|'desc'|null}
     */
    public function getValueFromRequest($request, $sortKey)
    {
        $sort = $request->string($sortKey);

        return match (true) {
            $sort->isEmpty() => [null, null],
            $sort->startsWith('-') => [$sort->after('-')->toString(), 'desc'],
            default => [$sort->toString(), 'asc'],
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
        return match (true) {
            $this->isSingularDirection() && $this->only === 'desc' => $this->getDescendingValue(),
            $this->isSingularDirection() => $this->getAscendingValue(),
            $this->direction === 'desc' => null,
            $this->direction === 'asc' => $this->getDescendingValue(),
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
        $this->only = 'asc';

        return $this;
    }

    /**
     * Set the sort to be descending.
     *
     * @return $this
     */
    public function desc()
    {
        $this->only = 'desc';

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
}
