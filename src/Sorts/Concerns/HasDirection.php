<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts\Concerns;

trait HasDirection
{
    public const ASCENDING = 'asc';

    public const DESCENDING = 'desc';

    /**
     * The order direction.
     *
     * @var 'asc'|'desc'|null
     */
    protected $direction;

    /**
     * Force the direction to be a specific value.
     *
     * @var 'asc'|'desc'|null
     */
    protected $enforced;

    /**
     * Whether the direction is inverted.
     *
     * @var bool
     */
    protected $invert = false;

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
        return $this->enforcesDirection()
            ? $this->enforced
            : $this->direction;
    }

    /**
     * Determine if the direction is ascending.
     *
     * @return bool
     */
    public function isAscending()
    {
        return $this->getDirection() === self::ASCENDING;
    }

    /**
     * Determine if the direction is descending.
     *
     * @return bool
     */
    public function isDescending()
    {
        return $this->getDirection() === self::DESCENDING;
    }

    /**
     * Force the direction to be always be ascending.
     *
     * @return $this
     */
    public function ascending()
    {
        $this->enforced = self::ASCENDING;

        return $this;
    }

    /**
     * Force the direction to be always be descending.
     *
     * @return $this
     */
    public function asc()
    {
        return $this->ascending();
    }

    /**
     * Force the direction to be always be descending.
     *
     * @return $this
     */
    public function descending()
    {
        $this->enforced = self::DESCENDING;

        return $this;
    }

    /**
     * Force the direction to be always be descending.
     *
     * @return $this
     */
    public function desc()
    {
        return $this->descending();
    }

    /**
     * Determine if the direction is enforced.
     *
     * @param  'asc'|'desc'|null  $direction
     * @return bool
     */
    public function enforcesDirection($direction = null)
    {
        if ($direction) {
            return $this->enforced === $direction;
        }

        return isset($this->enforced);
    }

    /**
     * Determine if the direction is not enforced.
     *
     * @return bool
     */
    public function isNotEnforced()
    {
        return ! $this->enforcesDirection();
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
}
