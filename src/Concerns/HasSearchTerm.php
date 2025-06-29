<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasSearchTerm
{
    public const SEARCH_TERM_DELIMITER = '+';

    /**
     * The search term as a string without replacements.
     *
     * @var string|null
     */
    protected $searchTerm = null;

    /**
     * Set the search term.
     *
     * @param  string|null  $term
     * @return void
     */
    public function setSearchTerm($term)
    {
        $this->searchTerm = $this->decodeSearchTerm($term);
    }

    /**
     * Retrieve the search term.
     *
     * @return string|null
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * Decode the search term to a string without replacements.
     *
     * @param  string|null  $term
     * @return string|null
     */
    public function decodeSearchTerm($term)
    {
        if (! $term) {
            return null;
        }

        return str_replace(self::SEARCH_TERM_DELIMITER, ' ', trim($term));
    }

    /**
     * Encode the search term to a string with replacements.
     *
     * @param  string|null  $term
     * @return string|null
     */
    public function encodeSearchTerm($term)
    {
        if (! $term) {
            return null;
        }

        return str_replace(' ', self::SEARCH_TERM_DELIMITER, trim($term));
    }
}
