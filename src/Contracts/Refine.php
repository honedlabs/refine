<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

use Honed\Core\Contracts\HooksIntoLifecycle;
use Honed\Core\Contracts\NullsAsUndefined;
use Honed\Persist\Contracts\CanPersistData;
use Honed\Refine\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * @method self persistSort(string|bool $driver = true)
 * @method self persistSortInSession()
 * @method self persistSortInCookie()
 * @method bool isPersistingSort()
 * @method \Honed\Persist\Drivers\Decorator|null getSortDriver()
 * @method self persistSearch(string|bool $driver = true)
 * @method self persistSearchInSession()
 * @method self persistSearchInCookie()
 * @method bool isPersistingSearch()
 * @method \Honed\Persist\Drivers\Decorator|null getSearchDriver()
 * @method self persistFilter(string|bool $driver = true)
 * @method self persistFilterInSession()
 * @method self persistFilterInCookie()
 * @method bool isPersistingFilter()
 * @method \Honed\Persist\Drivers\Decorator|null getFilterDriver()
 */
interface Refine extends CanPersistData, HooksIntoLifecycle, NullsAsUndefined
{
    /**
     * Get the request instance.
     */
    public function getRequest(): Request;

    /**
     * Get a builder instance of the resource.
     *
     * @return Builder<Model>
     *
     * @throws InvalidArgumentException
     */
    public function getBuilder(): Builder;

    /**
     * Get a model instance.
     *
     *
     * @throws InvalidArgumentException
     */
    public function getModel(): Model;

    /**
     * Get the delimiter.
     */
    public function getDelimiter(): string;

    /**
     * Format a value using the scope.
     */
    public function scoped(string $value): string;

    /**
     * Retrieve the filters.
     *
     * @return list<\Honed\Refine\Filters\Filter>
     */
    public function getFilters(): array;

    /**
     * Retrieve the sorts.
     *
     * @return list<Sort>
     */
    public function getSorts(): array;

    /**
     * Get the default sort.
     */
    public function getDefaultSort(): ?Sort;

    /**
     * Get the query parameter to identify the sort to apply.
     */
    public function getSortKey(): string;

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return list<\Honed\Refine\Searches\Search>
     */
    public function getSearches(): array;

    /**
     * Get the query parameter to identify the search.
     */
    public function getSearchKey(): string;

    /**
     * Set the search term.
     */
    public function setSearchTerm(?string $term): void;

    /**
     * Retrieve the search term.
     */
    public function getSearchTerm(): ?string;

    /**
     * Get the query parameter to identify the columns to search.
     */
    public function getMatchKey(): string;

    /**
     * Encode the search term to a string with replacements.
     */
    public function encodeSearchTerm(?string $term): ?string;

    /**
     * Decode the search term to a string without replacements.
     */
    public function decodeSearchTerm(?string $term): ?string;

    /**
     * Determine if the searches should be applied.
     */
    public function isSearchable(): bool;

    /**
     * Determine if Laravel Scout is being used for searching.
     */
    public function isScout(): bool;

    /**
     * Determine if matching is enabled
     */
    public function isMatchable(): bool;

    /**
     * Get the drivers being used for persisting data.
     *
     * @return array<string, \Honed\Persist\Drivers\Decorator>
     */
    public function getDrivers(): array;
}
