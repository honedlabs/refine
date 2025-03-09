<?php

declare(strict_types=1);

namespace Honed\Refine;

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
     * @param  array<int,mixed>|null  $columns
     * @param  string  $boolean
     * @return bool
     */
    public function refine($builder, $search, $columns, $boolean = 'and')
    {
        $shouldBeApplied = \is_null($columns) || \in_array($this->getParameter(), $columns);

        $this->value($shouldBeApplied ? $search : null);

        if (! $this->isActive()) {
            return false;
        }

        $value = type($search)->asString();

        $this->apply($builder, $value, $this->getName(), $boolean);

        return true;
    }

    /**
     * Add the search query scope to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string  $value
     * @param  string  $column
     * @param  string  $boolean
     * @return void
     */
    public function apply($builder, $value, $column, $boolean = 'and')
    {
        $column = $builder->qualifyColumn($column);

        $builder->whereRaw(
            sql: "LOWER({$column}) LIKE ?",
            bindings: ['%'.mb_strtolower($value, 'UTF8').'%'],
            boolean: $boolean,
        );
    }
}
