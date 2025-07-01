<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Interpret;
use Honed\Core\Pipe;
use Honed\Refine\Stores\Data\SortData;
use InvalidArgumentException;

/**
 * @template TClass of \Honed\Refine\Refine
 *
 * @extends Pipe<TClass>
 */
class SortQuery extends Pipe
{
    /**
     * Run the sort query logic.
     */
    public function run(): void
    {
        $builder = $this->instance->getBuilder();

        [$parameter, $direction] = $this->getValues();

        if ($this->sort($builder, $parameter, $direction)) {
            $this->persist($parameter, $direction);

            return;
        }

        $this->defaultSort($builder);
    }

    /**
     * Apply the sort to the resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return bool
     */
    protected function sort($builder, $parameter, $direction)
    {
        $applied = false;

        foreach ($this->instance->getSorts() as $sort) {
            if ($sort->handle($builder, $parameter, $direction)) {
                $applied = true;
            }
        }

        return $applied;
    }

    /**
     * Apply the default sort to the resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return void
     */
    protected function defaultSort($builder)
    {
        if ($sort = $this->instance->getDefaultSort()) {
            $parameter = $sort->getParameter();

            $sort->handle($builder, $parameter, null);
        }
    }

    /**
     * Get the sort name and direction from the request, or from a persisted
     * value.
     *
     * @return array{string|null, 'asc'|'desc'|null}
     */
    protected function getValues()
    {
        $request = $this->instance->getRequest();

        $key = $this->instance->getSortKey();

        [$parameter, $direction] = $this->getOrder($request, $key);

        return match (true) {
            (bool) $parameter => [$parameter, $direction],
            $request->missing($key) => $this->persisted($key),
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
    protected function getOrder($request, $key)
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
     * @param  string|null  $parameter
     * @param  'asc'|'desc'|null  $direction
     * @return void
     */
    protected function persist($parameter, $direction)
    {
        try {
            $data = SortData::from([
                'col' => $parameter,
                'dir' => $direction,
            ]);

            $this->instance->getSortDriver()?->put([
                $this->instance->getSortKey() => $data->toArray(),
            ]);
        } catch (InvalidArgumentException $e) {
        }
    }

    /**
     * Get the sort data from the store.
     *
     * @param  string  $key
     * @return array{string|null, 'asc'|'desc'|null}
     */
    protected function persisted($key)
    {
        try {
            $data = SortData::from(
                $this->instance->getSortDriver()?->get($key)
            );

            return [$data->column, $data->direction];
        } catch (InvalidArgumentException) {
            return [null, null];
        }
    }
}
