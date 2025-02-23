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

    public function setUp()
    {
        $this->type('sort');
    }

    /**
     * Determine if the sort is currently active.
     */
    public function isActive(): bool
    {
        return $this->getValue() === $this->getParameter();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function apply(Builder $builder, Request $request, string $sortKey): bool
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
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function handle(Builder $builder, string $direction, string $property): void
    {
        $builder->orderBy(
            column: $builder->qualifyColumn($property),
            direction: $direction,
        );
    }

    /**
     * Retrieve the sort value and direction from the request.
     *
     * @return array{0: string|null, 1: 'asc'|'desc'|null}
     */
    public function getValueFromRequest(Request $request, string $sortKey): array
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
    public function toArray(): array
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
    public function direction(?string $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get the direction to use for the query parameter.
     *
     * @return 'asc'|'desc'|null
     */
    public function getDirection(): ?string
    {
        return $this->isSingularDirection() ? $this->only : $this->direction;
    }

    /**
     * Get the next value to use for the query parameter.
     */
    public function getNextDirection(): ?string
    {
        return match (true) {
            $this->isSingularDirection() && $this->only === 'desc' => $this->getDescendingValue(),
            $this->isSingularDirection() => $this->getAscendingValue(),
            $this->direction === 'desc' => null,
            $this->direction === 'asc' => $this->getDescendingValue(),
            default => $this->getAscendingValue(),
        };
    }

    public function getDescendingValue(): string
    {
        return \sprintf('-%s', $this->getParameter());
    }

    public function getAscendingValue(): string
    {
        return $this->getParameter();
    }

    public function asc(): static
    {
        $this->only = 'asc';

        return $this;
    }

    public function desc(): static
    {
        $this->only = 'desc';

        return $this;
    }

    public function isSingularDirection(): bool
    {
        return ! \is_null($this->only);
    }
}
