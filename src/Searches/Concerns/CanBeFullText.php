<?php

declare(strict_types=1);

namespace Honed\Refine\Searches\Concerns;

trait CanBeFullText
{
    /**
     * Whether to use a full-text, recall search.
     *
     * @var bool
     */
    protected $fullText = false;

    /**
     * Set whether to use a full-text search.
     *
     * @param  bool  $value
     * @return $this
     */
    public function fullText($value = true)
    {
        $this->fullText = $value;

        return $this;
    }

    /**
     * Set whether to not use a full-text search.
     *
     * @param  bool  $value
     * @return $this
     */
    public function notFullText($value = true)
    {
        return $this->fullText(! $value);
    }

    /**
     * Determine if the search is a full-text search.
     *
     * @return bool
     */
    public function isFullText()
    {
        return $this->fullText;
    }

    /**
     * Determine if the search is not a full-text search.
     *
     * @return bool
     */
    public function isNotFullText()
    {
        return ! $this->fullText;
    }
}
