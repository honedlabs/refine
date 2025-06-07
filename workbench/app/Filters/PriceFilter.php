<?php

declare(strict_types=1);

namespace Workbench\App\Filters;

use Honed\Core\Contracts\WithQuery;
use Honed\Refine\Contracts\WithOptions;
use Honed\Refine\Filter;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
class PriceFilter extends Filter implements WithOptions, WithQuery
{
    /**
     * Provide the filter with any necessary setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->type('number');
        $this->name('price');
        $this->label('Price');
        $this->strict();
    }

    /**
     *  Create a new filter instance.
     *
     * @return static
     */
    public static function new()
    {
        return resolve(static::class);
    }

    /**
     * Register the options for the filter.
     *
     * @return array<int, string>
     */
    public function optionsUsing()
    {
        return [
            100 => 'Less than $100',
            999 => '$100 - $999',
            1000 => '$1000+',
        ];
    }

    /**
     * Register the query expression to apply the filter.
     *
     * @param  TBuilder  $builder
     * @param  mixed  $value
     * @return void
     */
    public function queryUsing($builder, $value)
    {
        match ($value) {
            100 => $builder->where('price', '<', 100),
            999 => $builder->where('price', '>=', 100)->where('price', '<=', 999),
            1000 => $builder->where('price', '>=', 1000),
        };
    }
}
