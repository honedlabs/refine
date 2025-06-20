<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

/**
 * @template TClass of \Honed\Refine\Refine
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
     * Get the sort name and direction from the request, or from a persisted
     * value.
     *
     * @param  TClass  $instance
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getValues($instance)
    {
        $request = $instance->getRequest();

        [$parameter, $direction] = $instance->getSortValue($request);

        return match (true) {
            (bool) $parameter => [$parameter, $direction],
            $instance->shouldPersistSort() => [null, null],
            default => [null, null]
        };
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
