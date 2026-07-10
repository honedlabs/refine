<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Interpret;
use Honed\Core\Pipe;
use Honed\Persist\Exceptions\DriverDataIntegrityException;
use Honed\Refine\Contracts\Refine;
use Honed\Refine\Contracts\UnionSearch;
use Honed\Refine\Data\SearchData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @extends Pipe<\Honed\Refine\Contracts\Refine&\Honed\Core\Primitive>
 */
class SearchQuery extends Pipe
{
    /**
     * Run the search query logic.
     */
    public function run(Refine $instance): void
    {
        if (! $instance->isSearchable()) {
            return;
        }

        [$term, $columns] = $this->getValues($instance);

        $instance->setSearchTerm($term);

        match (true) {
            $instance->isScout() => $this->scout($instance),
            $instance instanceof UnionSearch => $this->union($instance, $columns),
            default => $this->search($instance, $columns)
        };

        $this->persist($instance, $term, $columns);
    }

    /**
     * Set the search term, and if applicable, the search columns on the instance.
     *
     * @return array{string|null, array<int, string>|null}
     */
    public function getValues(Refine $instance): array
    {
        $request = $instance->getRequest();

        $key = $instance->getSearchKey();

        $term = Interpret::string($request, $key);

        $columns = $this->getColumns($instance, $request);

        return match (true) {
            $term || $columns => [
                $instance->encodeSearchTerm($term),
                $columns,
            ],
            $request->missing($key) => $this->persisted($instance, $key),
            default => [null, null]
        };
    }

    /**
     * Get the search columns from the instance's request.
     *
     * @return array<int, string>|null
     */
    public function getColumns(Refine $instance, Request $request): ?array
    {
        if (! $instance->isMatchable()) {
            return null;
        }

        return Interpret::array(
            $request,
            $instance->getMatchKey(),
            $instance->getDelimiter(),
            'string'
        );
    }

    /**
     * Perform the search using Scout.
     */
    public function scout(Refine $instance): void
    {
        $model = $instance->getModel();

        if (! $term = $instance->getSearchTerm()) {
            return;
        }

        $builder = $instance->getBuilder();

        $builder->whereIn(
            $builder->qualifyColumn($model->getKeyName()),
            // @phpstan-ignore-next-line method.notFound
            $model->search($term)->keys()
        );
    }

    /**
     * Perform the search using subquery searches on individual columns.
     *
     * @param  array<int, string>|null  $columns
     */
    public function union(Refine $instance, ?array $columns): void
    {
        $term = $instance->getSearchTerm();

        /** @var list<Builder<\Illuminate\Database\Eloquent\Model>> $unions */
        $unions = [];

        $applied = false;

        $builder = $instance->getBuilder();

        foreach ($instance->getSearches() as $search) {
            $query = $search->unionAs($builder);

            if ($search->handle($query, $term, $columns, $applied)) {
                $applied = true;

                $unions[] = $query;
            }
        }

        if (empty($unions)) {
            return;
        }

        $unionQuery = array_shift($unions)->getQuery();

        foreach ($unions as $union) {
            $unionQuery->unionAll($union);
        }

        $query = $builder->getQuery();
        $model = $builder->getModel();
        $table = $model->getTable();
        $keyName = $model->getKeyName();

        $existingJoins = $query->joins ?? [];
        $query->joins = [];

        $query->fromSub($unionQuery, '__honed_union')
            ->join($table, $model->qualifyColumn($keyName), '=', "__honed_union.{$keyName}");

        foreach ($existingJoins as $join) {
            $query->joins[] = $join;
        }
    }

    /**
     * Perform the search using the default search logic.
     *
     * @param  array<int,string>|null  $columns
     */
    public function search(Refine $instance, ?array $columns): bool
    {
        $term = $instance->getSearchTerm();

        $applied = false;

        $instance->getBuilder()->where(function ($query) use ($instance, $term, $columns, $applied) {
            foreach ($instance->getSearches() as $search) {
                if ($search->handle($query, $term, $columns, $applied)) {
                    $applied = true;
                }
            }
        });

        return $applied;
    }

    /**
     * Persist the search value to the internal data store.
     *
     * @param  array<int, string>|null  $columns
     */
    public function persist(Refine $instance, ?string $term, ?array $columns): void
    {
        try {
            $data = SearchData::make([
                'term' => $term,
                'cols' => $columns ?? [],
            ]);

            $instance->getSearchDriver()?->put(
                $instance->getSearchKey(), $data->toArray()
            );
        } catch (DriverDataIntegrityException $e) {
        }
    }

    /**
     * Get the search data from the store.
     *
     * @return array{string|null, array<int, string>|null}
     */
    public function persisted(Refine $instance, string $key): array
    {
        try {
            $data = SearchData::make(
                $instance->getSearchDriver()?->get($key)
            );

            $columns = $instance->isMatchable() ? $data->columns : [];

            return [$data->term, $columns];
        } catch (DriverDataIntegrityException $e) {
            return [null, null];
        }
    }
}
