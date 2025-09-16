<?php

declare(strict_types=1);

namespace Honed\Refine\Pipes;

use Honed\Core\Interpret;
use Honed\Core\Pipe;
use Honed\Persist\Exceptions\DriverDataIntegrityException;
use Honed\Refine\Data\SearchData;

/**
 * @template TClass of \Honed\Refine\Refine
 *
 * @extends Pipe<TClass>
 */
class SearchQuery extends Pipe
{
    /**
     * Run the search query logic.
     */
    public function run(): void
    {
        if ($this->instance->isNotSearchable()) {
            return;
        }

        [$term, $columns] = $this->getValues();

        $this->instance->setSearchTerm($term);

        $builder = $this->instance->getBuilder();

        match (true) {
            $this->instance->isScout() => $this->scout($builder),
            default => $this->search($builder, $columns)
        };

        $this->persist($term, $columns);
    }

    /**
     * Set the search term, and if applicable, the search columns on the instance.
     *
     * @return array{string|null, array<int,string>|null}
     */
    protected function getValues()
    {
        $request = $this->instance->getRequest();

        $key = $this->instance->getSearchKey();

        $term = Interpret::string($request, $key);

        $columns = $this->getColumns($request);

        return match (true) {
            $term || $columns => [
                $this->instance->encodeSearchTerm($term),
                $columns,
            ],
            $request->missing($key) => $this->persisted($key),
            default => [null, null]
        };
    }

    /**
     * Get the search columns from the instance's request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int,string>|null
     */
    protected function getColumns($request)
    {
        if ($this->instance->isNotMatchable()) {
            return null;
        }

        return Interpret::array(
            $request,
            $this->instance->getMatchKey(),
            $this->instance->getDelimiter(),
            'string'
        );
    }

    /**
     * Perform the search using Scout.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return void
     */
    protected function scout($builder)
    {
        $model = $this->instance->getModel();

        if (! $term = $this->instance->getSearchTerm()) {
            return;
        }

        $builder = $this->instance->getBuilder();

        $builder->whereIn(
            $builder->qualifyColumn($model->getKeyName()),
            // @phpstan-ignore-next-line method.notFound
            $model->search($term)->keys()
        );
    }

    /**
     * Perform the search using the default search logic.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  array<int,string>|null  $columns
     * @return bool
     */
    protected function search($builder, $columns)
    {
        $term = $this->instance->getSearchTerm();

        $applied = false;

        $builder->where(function ($query) use ($term, $columns, $applied) {
            foreach ($this->instance->getSearches() as $search) {
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
     * @param  string|null  $term
     * @param  array<int,string>|null  $columns
     * @return void
     */
    protected function persist($term, $columns)
    {
        try {
            $data = SearchData::make([
                'term' => $term,
                'cols' => $columns ?? [],
            ]);

            $this->instance->getSearchDriver()?->put(
                $this->instance->getSearchKey(), $data->toArray()
            );
        } catch (DriverDataIntegrityException $e) {
        }
    }

    /**
     * Get the search data from the store.
     *
     * @param  string  $key
     * @return array{string|null, array<int, string>|null}
     */
    protected function persisted($key)
    {
        try {
            $data = SearchData::make(
                $this->instance->getSearchDriver()?->get($key)
            );

            $columns = $this->instance->isMatchable() ? $data->columns : [];

            return [$data->term, $columns];
        } catch (DriverDataIntegrityException $e) {
            return [null, null];
        }
    }
}
