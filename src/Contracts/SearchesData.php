<?php

declare(strict_types=1);

namespace Honed\Refine\Contracts;

interface SearchesData
{
    /**
     * Determine if Laravel Scout is being used for searching.
     *
     * @return bool
     */
    public function isScout();

    /**
     * Retrieve the columns to be used for searching.
     *
     * @return array<int,\Honed\Refine\Searches\Search>
     */
    public function getSearches();

    /**
     * Get the query parameter to identify the search string.
     *
     * @return string
     */
    public function getSearchKey();

    /**
     * Get the query parameter to identify the columns to search.
     *
     * @return string
     */
    public function getMatchKey();

    /**
     * Determine if matching is enabled
     *
     * @return bool
     */
    public function isMatchable();

    /**
     * Set the search term.
     *
     * @param  string|null  $term
     * @return void
     */
    public function setTerm($term);

    /**
     * Retrieve the search term.
     *
     * @return string|null
     */
    public function getTerm();

    /**
     * Get a model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel();

    /**
     * Determine if the search should be persisted.
     *
     * @return bool
     */
    public function shouldPersistSearch();

    /**
     * Get the store to use for persisting searches.
     *
     * @return \Honed\Refine\Stores\Store|null
     */
    public function getSearchStore();
}
