<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait InterpretsRequest
{
    /**
     * The request interpreter.
     * 
     * @var string|null
     */
    protected $as;

    /**
     * Interpret the request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return mixed
     */
    public function interpret($request, $key)
    {
        return match ($this->as) {
            'array' => $request->safeArray($key)->toArray(),
            'boolean' => $request->safeBoolean($key),
            'date' => $request->safeDate($key),
            'float' => $request->safeFloat($key),
            'integer' => $request->safeInteger($key),
            'string' => $request->safeString($key),
            'time' => $request->safeDate($key),
            default => $request->safe($key),
        };
    }

    /**
     * Set the interpreter to use.
     * 
     * @param  string $as
     * @return $this
     */
    public function as($as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * Set the request to interpret as an array.
     * 
     * @return $this
     */
    public function asArray()
    {
        return $this->as('array');
    }

    /**
     * Set the request to interpret as a boolean.
     * 
     * @return $this
     */
    public function asBoolean()
    {
        return $this->as('boolean');
    }

    /**
     * Set the request to interpret as a date.
     * 
     * @return $this
     */
    public function asDate()
    {
        return $this->as('date');
    }

    /**
     * Set the request to interpret as a decimal. Alias for `asFloat`.
     * 
     * @return $this
     */
    public function asDecimal()
    {
        return $this->as('float');
    }

    /**
     * Set the request to interpret as a timestamp.
     * 
     * @return $this
     */
    public function asFloat()
    {
        return $this->as('float');
    }

    /**
     * Set the request to interpret as an integer.
     * 
     * @return $this
     */
    public function asInteger()
    {
        return $this->as('integer');
    }

    /**
     * Set the request to interpret as a string.
     * 
     * @return $this
     */
    public function asString()
    {
        return $this->as('string');
    }

    /**
     * Set the request to interpret as a time.
     * 
     * @return $this
     */
    public function asTime()
    {
        return $this->as('time');
    }

    /**
     * Get the interpreter.
     * 
     * @return string|null
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * Determine if the filter interprets an array.
     * 
     * @return bool
     */
    public function interpretsArray()
    {
        return $this->as === 'array';
    }

    /**
     * Determine if the filter interprets a boolean.
     * 
     * @return bool
     */
    public function interpretsBoolean()
    {
        return $this->as === 'boolean';
    }

    /**
     * Determine if the filter interprets a date.
     * 
     * @return bool
     */
    public function interpretsDate()
    {
        return $this->as === 'date';
    }

    /**
     * Determine if the filter interprets a float.
     * 
     * @return bool
     */
    public function interpretsFloat()
    {
        return $this->as === 'float';
    }

    /**
     * Determine if the filter interprets an integer.
     * 
     * @return bool
     */
    public function interpretsInteger()
    {
        return $this->as === 'integer';
    }

    /**
     * Determine if the filter interprets a string.
     * 
     * @return bool
     */
    public function interpretsString()
    {
        return $this->as === 'string';
    }

    /**
     * Determine if the filter interprets a time.
     * 
     * @return bool
     */
    public function interpretsTime()
    {
        return $this->as === 'time';
    }
}
