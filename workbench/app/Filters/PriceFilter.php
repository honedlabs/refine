<?php

declare(strict_types=1);

namespace Workbench\App\Filters;

use Honed\Refine\Filters\Filter;

class PriceFilter extends Filter
{
    /**
     * Create a new filter instance.
     *
     * @return static
     */
    public static function new()
    {
        return new self();
    }

    protected function definition(Filter $filter): Filter
    {
        return $filter
            ->name('price')
            ->type('price')
            ->label('Price')
            ->strict()
            ->options([
                100 => 'Less than $100',
                999 => '$100 - $999',
                1000 => '$1000+',
            ])
            ->query(function ($builder, $value) {
                match ($value) {
                    100 => $builder->where('price', '<', 100),
                    999 => $builder->where('price', '>=', 100)->where('price', '<=', 999),
                    1000 => $builder->where('price', '>=', 1000),
                };
            });
    }
}
