<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Interpret;
use Honed\Core\Pipe;

/**
 * @template TClass of \Honed\Refine\Contracts\RefinesData
 *
 * @extends Pipe<TClass>
 */
class SortQuery extends Pipe
{
    /**
     * Run the sort query logic.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function run($instance)
    {
        [$parameter, $direction] = $this->getValues($instance);

        $this->persist($instance, $parameter, $direction);

        if ($this->sort($instance, $parameter, $direction)) {
            $this->persist($instance, $parameter, $direction);

            return;
        }

        $this->defaultSort($instance);
    }

    /**
     * Apply the sort to the resource.
     *
     * @param  TClass  $instance
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return bool
     */
    public function sort($instance, $parameter, $direction)
    {
        $builder = $instance->getBuilder();

        $applied = false;

        foreach ($instance->getSorts() as $sort) {
            if ($sort->handle($builder, $parameter, $direction)) {
                $applied = true;
            }
        }

        return $applied;
    }

    /**
     * Apply the default sort to the resource.
     *
     * @param  TClass  $instance
     * @return void
     */
    public function defaultSort($instance)
    {
        $builder = $instance->getBuilder();

        if ($sort = $instance->getDefaultSort()) {
            $parameter = $sort->getParameter();

            $sort->handle($builder, $parameter, null);
        }
    }

    /**
     * Get the sort name and direction from the request, or from a persisted
     * value.
     *
     * @param  TClass  $instance
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getValues($instance)
    {
        $request = $instance->getRequest();

        $key = $instance->getSortKey();

        [$parameter, $direction] = $this->getOrder(
            $request, $key
        );

        return match (true) {
            (bool) $parameter => [$parameter, $direction],
            $instance->shouldPersistSort()
                && ! $request->has($key) => [null, null],
            default => [null, null]
        };
    }

    /**
     * Get the sort parameter from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getOrder($request, $key)
    {
        $sort = Interpret::string($request, $key);

        return match (true) {
            ! $sort => [null, null],
            str_starts_with($sort, '-') => [mb_substr($sort, 1), 'desc'],
            default => [$sort, 'asc'],
        };
    }

    /**
     * Persist the sort value to the internal data store.
     *
     * @param  TClass  $instance
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    public function persist($instance, $parameter, $direction)
    {
        $store = $instance->getSortStore();

        if (! $store) {
            return;
        }

        $store->put([
            'sort' => [
                'col' => $parameter,
                'dir' => $direction,
            ],
        ]);
    }
}
