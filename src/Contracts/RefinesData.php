<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

use Closure;

/**
 * @phpstan-require-extends \Honed\Core\Primitive
 */
interface RefinesData extends FiltersData, SearchesData, SortsData
{
    /**
     * Evaluate an expression with correct dependencies.
     *
     * @param  mixed  $value
     * @param  array<string, mixed>  $named
     * @param  array<class-string, mixed>  $typed
     * @return mixed
     */
    public function evaluate($value, $named = [], $typed = []);

    /**
     * Get the request instance.
     *
     * @return \Illuminate\Http\Request
     */
    public function getRequest();

    /**
     * Get a builder instance of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function getBuilder();

    /**
     * Get the callback to be executed before the refiners.
     *
     * @return Closure|null
     */
    public function getBeforeCallback();

    /**
     * Get the callback to be executed after refinement.
     *
     * @return Closure|null
     */
    public function getAfterCallback();

    /**
     * Get the delimiter.
     *
     * @return string
     */
    public function getDelimiter();

    /**
     * Persist the data to the stores.
     *
     * @return array<string,\Honed\Refine\Stores\Store>
     */
    public function getStores();

    /**
     * Format a value using the scope.
     *
     * @param  string  $value
     * @return string
     */
    public function formatScope($value);
}
