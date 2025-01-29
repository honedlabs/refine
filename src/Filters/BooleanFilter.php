<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BooleanFilter extends Filter
{
    public function setUp(): void
    {
        $this->type('boolean');
    }

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

    public function isActive(): bool
    {
        return (bool) $this->getValue();
    }

    public function getValueFromRequest(Request $request): bool // @phpstan-ignore-line
    {
        return $request->boolean($this->getParameter());
    }
}
