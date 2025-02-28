<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Carbon\Exceptions\InvalidFormatException;

class DateFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('date');
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Support\Carbon  $value
     */
    public function handle($builder, $value, $property)
    {
        $column = $builder->qualifyColumn($property);

        $builder->whereDate(
            column: $column,
            operator: $this->getOperator(),
            value: $value,
            boolean: 'and'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getValueFromRequest($request)
    {
        try {
            return $request->date($this->getParameter());
        } catch (InvalidFormatException $e) {
            return null;
        }
    }
}
