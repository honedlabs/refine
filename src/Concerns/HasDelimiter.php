<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasDelimiter
{
    /**
     * The delimiter to use for parsing array values.
     *
     * @var string|null
     */
    protected $delimiter;

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
     * Determine if the delimiter is set.
     *
     * @return bool
     */
    public function hasDelimiter()
    {
        return isset($this->delimiter);
    }

    /**
     * Get the delimiter.
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter ?? static::getDefaultDelimiter();
    }

    /**
     * Get the default delimiter.
     *
     * @return string
     */
    public static function getDefaultDelimiter()
    {
        return type(config('refine.delimiter', ','))->asString();
    }
}
