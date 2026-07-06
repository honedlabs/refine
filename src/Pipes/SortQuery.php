<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Interpret;
use Honed\Core\Pipe;
use Honed\Persist\Exceptions\DriverDataIntegrityException;
use Honed\Refine\Data\SortData;
use Honed\Refine\Refine;
use Illuminate\Http\Request;

/**
 * @extends Pipe<\Honed\Refine\Refine>
 */
class SortQuery extends Pipe
{
    /**
     * Run the sort query logic.
     */
    public function run(Refine $instance): void
    {
        [$parameter, $direction] = $this->getValues($instance);

        if ($this->sort($instance, $parameter, $direction)) {
            $this->persist($instance, $parameter, $direction);

            return;
        }

        $this->defaultSort($instance);
    }

    /**
     * Apply the sort to the resource.
     *
     * @param  'asc'|'desc'|null  $direction
     */
    public function sort(Refine $instance, ?string $parameter, ?string $direction): bool
    {
        $applied = false;

        $builder = $instance->getBuilder();

        foreach ($instance->getSorts() as $sort) {
            if ($sort->handle($builder, $parameter, $direction)) {
                $applied = true;
            }
        }

        return $applied;
    }

    /**
     * Apply the default sort to the resource.
     */
    public function defaultSort(Refine $instance): void
    {
        if ($sort = $instance->getDefaultSort()) {
            $parameter = $sort->getParameter();

            $sort->handle($instance->getBuilder(), $parameter, null);

            $sort->active();
        }
    }

    /**
     * Get the sort name and direction from the request, or from a persisted
     * value.
     *
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getValues(Refine $instance): array
    {
        $request = $instance->getRequest();
        $key = $instance->getSortKey();

        [$parameter, $direction] = $this->getOrder($request, $key);

        return match (true) {
            (bool) $parameter => [$parameter, $direction],
            $request->missing($key) => $this->persisted($instance, $key),
            default => [null, null]
        };
    }

    /**
     * Get the sort parameter from the request.
     *
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function getOrder(Request $request, string $key): array
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
     * @param  'asc'|'desc'|null  $direction
     */
    public function persist(Refine $instance, ?string $parameter, ?string $direction): void
    {
        try {
            $data = SortData::make([
                'col' => $parameter,
                'dir' => $direction,
            ]);

            $instance->getSortDriver()?->put(
                $instance->getSortKey(), $data->toArray()
            );
        } catch (DriverDataIntegrityException $e) {
        }
    }

    /**
     * Get the sort data from the store.
     *
     * @return array{string|null, 'asc'|'desc'|null}
     */
    public function persisted(Refine $instance, string $key): array
    {
        try {
            $data = SortData::make(
                $instance->getSortDriver()?->get($key)
            );

            return [$data->column, $data->direction];
        } catch (DriverDataIntegrityException) {
            return [null, null];
        }
    }
}
