<?php

declare(strict_types=1);

namespace Honed\Refine\Searches\Concerns;

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
     * @var bool
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
     * The search term as a string without replacements.
     *
     * @var string|null
     */
    protected $term = null;

    /**
     * The placeholder to use for the search bar.
     *
     * @var string|null
     */
    protected $searchPlaceholder = null;

    /**
     * Set whether the searches should be applied.
     *
     * @param  bool  $enable
     * @return $this
     */
    public function searchable($enable = true)
    {
        $this->searchable = $enable;

        return $this;
    }

    /**
     * Set whether the searches should not be applied.
     *
     * @param  bool  $disable
     * @return $this
     */
    public function notSearchable($disable = true)
    {
        return $this->searchable(! $disable);
    }

    /**
     * Determine if the searches should be applied.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * Determine if the searches should not be applied.
     *
     * @return bool
     */
    public function isNotSearchable()
    {
        return ! $this->isSearchable();
    }

    /**
     * Set whether the search columns can be toggled.
     *
     * @param  bool  $enable
     * @return $this
     */
    public function matchable($enable = true)
    {
        $this->match = $enable;

        return $this;
    }

    /**
     * Set whether the search columns can not be toggled.
     *
     * @param  bool  $disable
     * @return $this
     */
    public function notMatchable($disable = true)
    {
        return $this->matchable(! $disable);
    }

    /**
     * Determine if matching is enabled
     *
     * @return bool
     */
    public function isMatchable()
    {
        return $this->match && $this->isNotScout();
    }

    /**
     * Determine if matching is not enabled.
     *
     * @return bool
     */
    public function isNotMatchable()
    {
        return ! $this->isMatchable();
    }

    /**
     * Set whether to use Laravel Scout for searching.
     *
     * @param  bool  $scout
     * @return $this
     */
    public function scout($scout = true)
    {
        $this->scout = $scout;

        return $this;
    }

    /**
     * Determine if Laravel Scout is being used for searching.
     *
     * @return bool
     */
    public function isScout()
    {
        return $this->scout;
    }

    /**
     * Determine if Laravel Scout is not being used for searching.
     *
     * @return bool
     */
    public function isNotScout()
    {
        return ! $this->isScout();
    }

    /**
     * Merge a set of searches with the existing searches.
     *
     * @param  Search|array<int, Search>  $searches
     * @return $this
     */
    public function searches($searches)
    {
        /** @var array<int, Search> $searches */
        $searches = is_array($searches) ? $searches : func_get_args();

        $this->searches = [...$this->searches, ...$searches];

        return $this;
    }

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,Search>
     */
    public function getSearches()
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
     * @param  string  $searchKey
     * @return $this
     */
    public function searchKey($searchKey)
    {
        $this->searchKey = $searchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the search.
     *
     * @return string
     */
    public function getSearchKey()
    {
        return $this->formatScope($this->searchKey);
    }

    /**
     * Set the query parameter to identify the columns to search.
     *
     * @param  string  $matchKey
     * @return $this
     */
    public function matchKey($matchKey)
    {
        $this->matchKey = $matchKey;

        return $this;
    }

    /**
     * Get the query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchKey()
    {
        return $this->formatScope($this->matchKey);
    }

    /**
     * Set the search term.
     *
     * @param  string|null  $term
     * @return $this
     */
    public function term($term)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Retrieve the search term.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
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
     *
     * @return bool
     */
    public function isSearching()
    {
        return filled($this->getTerm());
    }

    /**
     * Get the searches as an array.
     *
     * @return array<int,array<string,mixed>>
     */
    public function searchesToArray()
    {
        if ($this->isNotMatchable()) {
            return [];
        }

        return array_values(
            array_map(
                static fn (Search $search) => $search->toArray(),
                array_filter(
                    $this->getSearches(),
                    static fn (Search $search) => $search->isVisible()
                )
            )
        );
    }
}
