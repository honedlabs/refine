<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Refiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Search extends Refiner
{
    public function setUp()
    {
        $this->type('search');
    }

    public function isActive(): bool
    {
        return $this->hasValue();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<int,string>|true  $columns
     */
    public function apply(Builder $builder, Request $request, string $searchKey, array|true $columns, bool $and): bool
    {
        $value = $this->getValueFromRequest($request, $searchKey);

        $shouldBeApplied = $columns === true || \in_array($this->getParameter(), $columns);

        $this->value($shouldBeApplied ? $value : null);

        if (! $this->isActive()) {
            return false;
        }

        $attribute = type($this->getAttribute())->asString();
        $value = type($value)->asString();

        $this->handle($builder, $value, $attribute, $and);

        return true;
    }

    public function getValueFromRequest(Request $request, string $searchKey): ?string
    {
        $v = $request->string($searchKey)->toString();

        if (empty($v)) {
            return null;
        }

        return $v;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function handle(Builder $builder, string $value, string $property, bool $boolean): void
    {
        $builder->where(
            column: $builder->qualifyColumn($property),
            operator: 'like',
            value: '%'.$value.'%',
            boolean: $boolean ? 'and' : 'or',
        );
    }
}
