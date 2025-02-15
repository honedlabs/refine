<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BooleanFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->type('boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Builder $builder, mixed $value, string $property): void
    {
        $column = $builder->qualifyColumn($property);

        $builder->where(
            column: $column,
            operator: self::Is,
            value: $value,
            boolean: 'and'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromRequest(Request $request): bool // @phpstan-ignore-line
    {
        return $request->boolean($this->getParameter());
    }
}
