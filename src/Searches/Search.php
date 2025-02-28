<?php

declare(strict_types=1);

namespace Honed\Refine\Searches;

use Honed\Refine\Refiner;
use Illuminate\Database\Eloquent\Builder;

class Search extends Refiner
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('search');
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueKey()
    {
        return type($this->getAttribute())->asString();
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->hasValue();
    }

    /**
     * Search the builder using the request.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string|null  $search
     * @param  array<int,string>|true  $columns
     * @param  string  $boolean
     * @return bool
     */
    public function apply($builder, $search, $columns, $boolean = 'and')
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
     * Add the search query scope to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string  $value
     * @param  string  $property
     * @param  string  $boolean
     * @return void
     */
    public function handle($builder, $value, $property, $boolean = 'and')
    {
        $qualified = $builder->qualifyColumn($property);

        $builder->whereRaw(
            sql: "LOWER({$qualified}) LIKE ?",
            bindings: ['%'.mb_strtolower($value, 'UTF8').'%'],
            boolean: $boolean,
        );
    }
}
