<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

/**
 * @template TClass of \Honed\Refine\Refine
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
            $value = $instance->getFilterValue($request, $filter);

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
}
