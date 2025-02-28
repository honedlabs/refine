<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Core\Concerns\HasRequest;
use Honed\Core\Concerns\HasScope;

trait AccessesRequest
{
    use HasRequest;
    use HasScope;

    /**
     * The delimiter for accessing arrays.
     *
     * @var string|null
     */
    protected $delimiter;

    /**
     * Set the delimiter for accessing arrays.
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
     * Get the delimiter for accessing arrays.
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter ?? $this->getFallbackDelimiter();
    }

    /**
     * Get the fallback delimiter for accessing arrays.
     *
     * @return string
     */
    protected function getFallbackDelimiter()
    {
        return type(config('refine.delimiter', ','))->asString();
    }

    /**
     * Get a query parameter from the request using the current scope.
     *
     * @param  string  $parameter
     * @return mixed
     */
    public function getScopedQueryParameter($parameter)
    {
        $scoped = $this->formatScope($parameter);

        return $this->getQueryParameter($scoped);
    }

    /**
     * Use the delimiter to extract an array of values from a query parameter.
     *
     * @param  string  $parameter
     * @return array<int,string>|null
     */
    public function getArrayFromQueryParameter($parameter)
    {
        /** @var string|null */
        $param = $this->getScopedQueryParameter($parameter);

        $values = str($param);

        if ($values->isEmpty()) {
            return null;
        }

        /** @var array<int,string> */
        return $values
            ->explode($this->getDelimiter())
            ->map(fn ($value) => \trim($value))
            ->filter()
            ->values()
            ->toArray();
    }
}
