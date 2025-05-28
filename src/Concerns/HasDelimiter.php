<?php

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
     * The default delimiter to use for parsing array values.
     *
     * @var string
     */
    protected static $useDelimiter = ',';

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
        return $this->delimiter ?? static::$useDelimiter;
    }

    /**
     * Set the delimiter to use by default.
     *
     * @param  string  $delimiter
     * @return void
     */
    public static function useDelimiter($delimiter = ',')
    {
        static::$useDelimiter = $delimiter;
    }
}
