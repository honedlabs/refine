<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasSearchTerm
{
    public const SEARCH_TERM_DELIMITER = '+';

    /**
     * The search term as a string without replacements.
     */
    protected ?string $searchTerm = null;

    /**
     * Set the search term.
     */
    public function setSearchTerm(?string $term): void
    {
        $this->searchTerm = $this->decodeSearchTerm($term);
    }

    /**
     * Retrieve the search term.
     */
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    /**
     * Decode the search term to a string without replacements.
     */
    public function decodeSearchTerm(?string $term): ?string
    {
        if (! $term) {
            return null;
        }

        return str_replace(self::SEARCH_TERM_DELIMITER, ' ', trim($term));
    }

    /**
     * Encode the search term to a string with replacements.
     */
    public function encodeSearchTerm(?string $term): ?string
    {
        if (! $term) {
            return null;
        }

        return str_replace(' ', self::SEARCH_TERM_DELIMITER, trim($term));
    }
}
