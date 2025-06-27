<?php

declare(strict_types=1);

namespace Honed\Refine\Stores\Data;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class StoreData implements Arrayable, JsonSerializable
{
    /**
     * Attempt to create the object from a given value.
     *
     * @param  mixed  $value
     * @return static|null
     *
     * @throws InvalidArgumentException
     */
    abstract public static function try($value);

    /**
     * Create a new store data object.
     *
     * @param  mixed  $value
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public static function from($value)
    {
        if ($data = static::try($value)) {
            return $data;
        }

        static::fail();
    }

    /**
     * Fail the creation of the store data object.
     *
     * @return never
     *
     * @throws InvalidArgumentException
     */
    public static function fail()
    {
        throw new InvalidArgumentException(
            'The structure was not able to be created.'
        );
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
