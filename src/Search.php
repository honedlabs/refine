<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Refine\Refiner;

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
    public function isActive()
    {
        return $this->hasValue();
    }

    /**
     * Search the builder using the request.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string|null  $search
     * @param  array<int,string>|null  $columns
     * @param  string  $boolean
     * @return bool
     */
    public function apply($builder, $search, $columns, $boolean = 'and')
    {
        $shouldBeApplied = \is_null($columns) || \in_array($this->getParameter(), $columns);

        $this->value($shouldBeApplied ? $search : null);

        if (! $this->isActive()) {
            return false;
        }

        $value = type($search)->asString();

        $this->handle($builder, $value, $this->getName(), $boolean);

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
