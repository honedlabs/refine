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
        return $this->delimiter ?? static::fallbackDelimiter();
    }

    /**
     * Get the delimiter from the config.
     *
     * @return string
     */
    public static function fallbackDelimiter()
    {
        return type(config('refine.delimiter', ','))->asString();
    }
}
