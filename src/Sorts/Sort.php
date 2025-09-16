<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

use Honed\Core\Concerns\CanBeDefault;
use Honed\Refine\Refiner;
use Honed\Refine\Sorts\Concerns\HasDirection;

use function array_merge;
use function sprintf;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Refiner<TModel, TBuilder>
 */
class Sort extends Refiner
{
    use CanBeDefault;
    use HasDirection;

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'sort';

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

        if ($this->enforcesDirection()) {
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
        return match (true) {
            $this->enforcesDirection() => $this->getEnforcedDirection(),
            $this->isInverted() => $this->getInvertedValue(),
            default => match (true) {
                $this->isAscending() => $this->getDescendingValue(),
                $this->isDescending() => null,
                default => $this->getAscendingValue(),
            },
        };
    }

    /**
     * Handle the sorting of the query.
     *
     * @param  TBuilder  $query
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return bool
     */
    public function handle($query, $parameter, $direction)
    {
        $this->checkIfActive($parameter, $direction);

        if (! $this->isActive()) {
            return false;
        }

        if (! $this->enforcesDirection()) {
            $this->direction($direction);
        }

        return $this->refine($query, [
            ...$this->getBindings($query),
            'direction' => $this->getDirection(),
            'parameter' => $parameter,
        ]);
    }

    /**
     *  Add a sort scope to the query.
     *
     * @param  TBuilder  $query
     * @param  string  $column
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    public function apply($query, $column, $direction)
    {
        $query->orderBy($column, $direction ?? self::ASCENDING);
    }

    /**
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        return array_merge(parent::representation(), [
            'direction' => $this->getDirection(),
            'next' => $this->getNextDirection(),
        ]);
    }

    /**
     * Get the fixed direction.
     *
     * @return string|null
     */
    protected function getEnforcedDirection()
    {
        if ($this->isNotEnforced()) {
            return null;
        }

        return $this->enforcesDirection(self::ASCENDING)
            ? $this->getAscendingValue()
            : $this->getDescendingValue();
    }

    /**
     * Get the inverted value.
     *
     * @return string|null
     */
    protected function getInvertedValue()
    {
        return match (true) {
            $this->isAscending() => null,
            $this->isDescending() => $this->getAscendingValue(),
            default => $this->getDescendingValue(),
        };
    }

    /**
     * Guess the parameter for the sort.
     *
     * @return string
     */
    protected function guessParameter()
    {
        return $this->enforcesDirection()
            ? sprintf('%s_%s', parent::guessParameter(), $this->enforced)
            : parent::guessParameter();
    }

    /**
     * Determine if the sort is active.
     *
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    protected function checkIfActive($parameter, $direction)
    {
        $this->active(
            $parameter === $this->getParameter() && (
                $this->enforcesDirection()
                    ? true
                    : $direction === $this->getDirection()
            )
        );
    }
}
