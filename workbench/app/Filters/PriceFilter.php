<?php

namespace Workbench\App\Filters;

use Honed\Refine\Filter;
use Illuminate\Database\Eloquent\Builder;
use Honed\Refine\Contracts\FromOptions;
use Honed\Core\Contracts\FromQuery;
use Honed\Core\Contracts\WithQuery;
use Honed\Refine\Contracts\WithOptions;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model = \Illuminate\Database\Eloquent\Model
 * @template TBuilder of \Illuminate\Database\Eloquent\Builder<TModel> = \Illuminate\Database\Eloquent\Builder<TModel>
 *
 * @extends \Honed\Refine\Filter<TModel, TBuilder>
 */
class PriceFilter extends Filter implements WithOptions, WithQuery
{
    /**
     *  Create a new filter instance.
     *
     *  @return static
     */
    public static function new()
    {
        return resolve(static::class);
    }

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