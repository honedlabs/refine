<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Closure;
use Honed\Refine\Searches\Search;

use function array_filter;
use function array_map;
use function array_values;

trait HasSearches
{
    /**
     * Whether the searches should be applied.
     *
     * @var bool
     */
    protected $searchable = true;

    /**
     * Whether the search columns can be toggled.
     *
     * @var bool
     */
    protected $match = false;

    /**
     * Indicate whether to use Laravel Scout for searching.
     *
     * @var bool|Closure(mixed...):bool
     */
    protected $scout = false;

    /**
     * List of the searches.
     *
     * @var array<int,Search>
     */
    protected $searches = [];

    /**
     * The query parameter to identify the search string.
     *
     * @var string
     */
    protected $searchKey = 'search';

    /**
     * The query parameter to identify the columns to search on.
     *
     * @var string
     */
    protected $matchKey = 'match';

    /**
     * The placeholder to use for the search bar.
     *
     * @var string|null
     */
    protected $searchPlaceholder = null;

    /**
     * Set whether the searches should be applied.
     *
     * @return $this
     */
    public function searchable(bool $value = true): static
    {
        $this->searchable = $value;

        return $this;
    }

    /**
     * Set whether the searches should not be applied.
     *
     * @return $this
     */
    public function notSearchable(bool $value = true): static
    {
        return $this->searchable(! $value);
    }

    /**
     * Determine if the searches should be applied.
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Determine if the searches should not be applied.
     */
    public function isNotSearchable(): bool
    {
        return ! $this->isSearchable();
    }

    /**
     * Set whether the search columns can be toggled.
     *
     * @return $this
     */
    public function matchable(bool $value = true): static
    {
        $this->match = $value;

        return $this;
    }

    /**
     * Set whether the search columns can not be toggled.
     *
     * @return $this
     */
    public function notMatchable(bool $value = true): static
    {
        return $this->matchable(! $value);
    }

    /**
     * Determine if matching is enabled
     */
    public function isMatchable(): bool
    {
        return $this->match && $this->isNotScout();
    }

    /**
     * Determine if matching is not enabled.
     */
    public function isNotMatchable(): bool
    {
        return ! $this->isMatchable();
    }

    /**
     * Set whether to use Laravel Scout for searching.
     *
     * @return $this
     */
    public function scout(bool $value = true): static
    {
        $this->scout = $value;

        return $this;
    }

    /**
     * Set whether to not use Laravel Scout for searching.
     *
     * @return $this
     */
    public function notScout(bool $value = true): static
    {
        return $this->scout(! $value);
    }

    /**
     * Determine if Laravel Scout is being used for searching.
     */
    public function isScout(): bool
    {
        return (bool) $this->evaluate($this->scout);
    }

    /**
     * Determine if Laravel Scout is not being used for searching.
     */
    public function isNotScout(): bool
    {
        return ! $this->isScout();
    }

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  Search|array<int, Search>  $searches
     * @return $this
     */
    public function searches(Search|array $searches): static
    {
        /** @var array<int, Search> $searches */
        $searches = is_array($searches) ? $searches : func_get_args();

        $this->searches = [...$this->searches, ...$searches];

        return $this;
    }

    /**
     * Insert a search.
     *
     * @return $this
     */
    public function search(Search $search): static
    {
        $this->searches[] = $search;

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,Search>
     */
    public function getSearches(): array
    {
        if ($this->isNotSearchable()) {
            return [];
        }

        return once(fn () => array_values(
            array_filter(
                $this->searches,
                static fn (Search $search) => $search->isAllowed()
            )
        ));
    }

    /**
     * Set the query parameter to identify the search string.
     *
     * @return $this
     */
    public function searchKey(string $searchKey): static
    {
        $this->searchKey = $searchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the search.
     */
    public function getSearchKey(): string
    {
        return $this->scoped($this->searchKey);
    }

    /**
     * Set the query parameter to identify the columns to search.
     *
     * @return $this
     */
    public function matchKey(string $matchKey): static
    {
        $this->matchKey = $matchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the columns to search.
     */
    public function getMatchKey(): string
    {
        return $this->scoped($this->matchKey);
    }

    /**
     * Set the placeholder text to use for the search bar.
     *
     * @param  string|null  $placeholder
     * @return $this
     */
    public function searchPlaceholder($placeholder)
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    /**
     * Get the placeholder text to use for the search bar.
     *
     * @return string|null
     */
    public function getSearchPlaceholder()
    {
        return $this->searchPlaceholder;
    }

    /**
     * Determine if there is a search being applied.
     */
    public function isSearching(): bool
    {
        return filled($this->getSearchTerm());
    }

    /**
     * Determine if there is no search being applied.
     */
    public function isNotSearching(): bool
    {
        return ! $this->isSearching();
    }

    /**
     * Get the searches being applied.
     *
     * @return array<int,Search>
     */
    public function getActiveSearches(): array
    {
        return array_values(
            array_filter(
                $this->getSearches(),
                static fn (Search $search) => $search->isActive()
            )
        );
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray(): array
    {
        if ($this->isNotMatchable()) {
            return [];
        }

        return array_values(
            array_map(
                static fn (Search $search) => $search->toArray(),
                array_filter(
                    $this->getSearches(),
                    static fn (Search $search) => $search->isNotHidden()
                )
            )
        );
    }
}
