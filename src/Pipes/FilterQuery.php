<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Closure;
use Honed\Core\Pipe;
use Honed\Refine\Filters\Filter;
use Honed\Refine\Refine;

/**
 * @extends \Honed\Core\Pipe<\Honed\Refine\Refine>
 */
class FilterQuery extends Pipe
{
    /**
     * Run the filter query logic.
     */
    public function run(Refine $instance): void
    {
        if ($this->filter($instance, $this->getFilterValue(...))) {
            return;
        }

        $this->filter($instance, $this->getPersistedValue(...));
    }

    /**
     * Apply the filters using the request values.
     *
     * @param  Closure(Refine, string, Filter):mixed  $callback
     */
    public function filter(Refine $instance, Closure $callback): bool
    {
        $applied = false;
        $builder = $instance->getBuilder();

        foreach ($instance->getFilters() as $filter) {
            $key = $instance->scoped($filter->getParameter());

            $value = $callback($instance, $key, $filter);

            if ($filter->handle($builder, $value)) {
                $applied = true;

                $instance->getFilterDriver()?->put($key, $value);
            }
        }

        return $applied;
    }

    /**
     * Get the filter value from the request.
     */
    public function getFilterValue(Refine $instance, string $key, Filter $filter): mixed
    {

        return $filter->interpret(
            $instance->getRequest(), $key, $instance->getDelimiter()
        );
    }

    /**
     * Get the sort data from the store.
     */
    public function getPersistedValue(Refine $instance, string $key): mixed
    {
        return $instance->getFilterDriver()?->get($key);
    }
}
