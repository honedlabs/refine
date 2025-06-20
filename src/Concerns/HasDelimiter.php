<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasDelimiter
{
    /**
     * The delimiter to use for parsing array values.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Set the delimiter.
     *
     * @param  string  $delimiter
     * @return $this
     */
    public function delimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get the delimiter.
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }
}
