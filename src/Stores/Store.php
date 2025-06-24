<?php

declare(strict_types=1);

namespace Honed\Refine\Stores;

use Illuminate\Support\Arr;

abstract class Store
{
    /**
     * The data to persist.
     *
     * @var array<string,mixed>
     */
    protected $data = [];

    /**
     * The resolved data from the store.
     *
     * @var array<string,mixed>|null
     */
    protected $resolved;

    /**
     * The key to be used for the instance.
     *
     * @var string
     */
    protected $key;

    /**
     * Retrieve the data from the store and set it in memory.
     *
     * @return $this
     */
    abstract public function resolve();

    /**
     * Persist the data to the session.
     *
     * @return void
     */
    abstract public function persist();

    /**
     * Create a new instance of the store.
     *
     * @param  string  $key
     * @return static
     */
    public static function make($key)
    {
        return resolve(static::class)
            ->key($key)
            ->resolve();
    }

    /**
     * Set the key to be used for the store.
     *
     * @param  string  $key
     * @return $this
     */
    public function key($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get a value from the resolved data.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (! $this->resolved) {
            $this->resolve();
        }

        return $key ? Arr::get($this->resolved ?? [], $key, null) : $this->resolved;
    }

    /**
     * Put the value for the given key in to an internal data store in preparation
     * to persist it.
     *
     * @param  string|array<string,mixed>  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = [...$this->data, ...$key];
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }
}
