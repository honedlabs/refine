<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Refine
 *
 * @extends Pipe<TClass>
 */
class FilterQuery extends Pipe
{
    /**
     * Run the filter query logic.
     */
    public function run(): void
    {
        $builder = $this->instance->getBuilder();

        if ($this->filter($builder, $this->getFilterValue(...))) {
            return;
        }

        $this->filter($builder, $this->persisted(...));
    }

    /**
     * Apply the filters using the request values.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  callable(string, \Honed\Refine\Filters\Filter):mixed  $callback
     * @return bool
     */
    protected function filter($builder, $callback)
    {
        $applied = false;

        foreach ($this->instance->getFilters() as $filter) {
            $key = $this->getParameter($filter);

            $value = $callback($key, $filter);

            if ($filter->handle($builder, $value)) {
                $applied = true;

                $this->persist($key, $value);
            }
        }

        return $applied;
    }

    /**
     * Get the filter value from the request.
     *
     * @param  string  $key
     * @param  \Honed\Refine\Filters\Filter  $filter
     * @return mixed
     */
    protected function getFilterValue($key, $filter)
    {

        return $filter->interpret(
            $this->instance->getRequest(),
            $key,
            $this->instance->getDelimiter()
        );
    }

    /**
     * Get the parameter of the filter.
     *
     * @param  \Honed\Refine\Filters\Filter  $filter
     * @return string
     */
    protected function getParameter($filter)
    {
        return $this->instance->scoped($filter->getParameter());
    }

    /**
     * Persist the filter value to the internal data store.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    protected function persist($key, $value)
    {
        $this->instance->getFilterDriver()?->put([$key => $value]);
    }

    /**
     * Get the sort data from the store.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function persisted($key)
    {
        return $this->instance->getFilterDriver()?->get($key);
    }
}
