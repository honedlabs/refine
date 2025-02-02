<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Refiner;
use Illuminate\Database\Eloquent\Builder;

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
    public function apply(Builder $builder, ?string $search, array|true $columns, string $boolean = 'and'): bool
    {
        $shouldBeApplied = $columns === true || \in_array($this->getParameter(), $columns);

        $this->value($shouldBeApplied ? $search : null);

        if (! $this->isActive()) {
            return false;
        }

        $attribute = type($this->getAttribute())->asString();

        $value = type($search)->asString();

        $this->handle($builder, $value, $attribute, $boolean);

        return true;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     */
    public function handle(Builder $builder, string $value, string $property, string $boolean = 'and'): void
    {
        $qualified = $builder->qualifyColumn($property);

        $builder->whereRaw(
            sql: "LOWER({$qualified}) LIKE ?",
            bindings: ['%'.mb_strtolower($value, 'UTF8').'%'],
            boolean: $boolean,
        );
    }
}
