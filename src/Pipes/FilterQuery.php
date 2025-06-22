<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Contracts\RefinesData
 *
 * @extends Pipe<TClass>
 */
class FilterQuery extends Pipe
{
    /**
     * Run the after refining logic.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function run($instance)
    {
        if ($this->filter($instance)) {
            return;
        }

        // $this->persistedFilter($instance);
    }

    /**
     * Apply the filters using the request values.
     *
     * @param  TClass  $instance
     * @return bool
     */
    public function filter($instance)
    {
        $builder = $instance->getBuilder();

        $request = $instance->getRequest();

        $applied = false;

        foreach ($instance->getFilters() as $filter) {
            $value = $this->getRequestValue($instance, $request, $filter);

            if ($filter->handle($builder, $value)) {
                $applied = true;

                // $this->persist($instance, $filter, $value);
            }
        }

        return $applied;
    }

    /**
     * Apply the filters using the persisted values.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function persistedFilter($instance)
    {
        // $builder = $instance->getBuilder();

        foreach ($instance->getFilters() as $filter) {
            // $value = $instance->getPersistedFilterValue($filter);

            // if ($filter->handle($builder, $value)) {
            //     $instance->persistFilter($filter, $value);
            // }
        }
    }

    /**
     * Get the filter value from the request.
     *
     * @param  TClass  $instance
     * @param  \Illuminate\Http\Request  $request
     * @param  \Honed\Refine\Filters\Filter  $filter
     * @return mixed
     */
    protected function getRequestValue($instance, $request, $filter)
    {
        $key = $instance->formatScope($filter->getParameter());

        $delimiter = $instance->getDelimiter();

        return $filter->interpret($request, $key, $delimiter);
    }
}
