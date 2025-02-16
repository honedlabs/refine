<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DateFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->type('date');
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Support\Carbon  $value
     */
    public function handle(Builder $builder, mixed $value, string $property): void
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
    public function getValueFromRequest(Request $request): mixed
    {
        try {
            return $request->date($this->getParameter());
        } catch (InvalidFormatException $e) {
            return null;
        }
    }
}
