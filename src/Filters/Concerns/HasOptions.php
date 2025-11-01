<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use BackedEnum;
use Honed\Core\Contracts\HasLabel;
use Honed\Refine\Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function is_string;

trait HasOptions
{
    /**
     * The available options.
     *
     * @var array<int, Option>
     */
    protected $options = [];

    /**
     * Whether to restrict options to only those provided.
     *
     * @var bool
     */
    protected $strict = false;

    /**
     * Whether to accept multiple values.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Set the options for the filter.
     *
     * @template TValue of bool|float|int|string|null|\Honed\Refine\Option
     *
     * @param  class-string<BackedEnum>|array<int|string,TValue>|Collection<int|string,TValue>  $options
     * @return $this
     */
    public function options($options)
    {
        $this->options = $this->createOptions($options);

        return $this;
    }

    /**
     * Get the options.
     *
     * @return array<int,Option>
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Determine if the filter has options.
     *
     * @return bool
     */
    public function hasOptions()
    {
        return filled($this->getOptions());
    }

    /**
     * Restrict the options to only those provided.
     *
     * @param  bool  $strict
     * @return $this
     */
    public function strict($strict = true)
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Allow any options to be used.
     *
     * @param  bool  $lax
     * @return $this
     */
    public function lax($lax = true)
    {
        return $this->strict(! $lax);
    }

    /**
     * Determine if only the options provided are allowed.
     *
     * @return bool
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Allow multiple options to be used.
     *
     * @param  bool  $multiple
     * @return $this
     */
    public function multiple($multiple = true)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Determine if multiple options are allowed.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Determine if only one option is allowed.
     *
     * @return bool
     */
    public function isNotMultiple()
    {
        return ! $this->isMultiple();
    }

    /**
     * Activate the options and return the valid options.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function activateOptions($value)
    {
        $options = array_values(
            array_filter(
                $this->getOptions(),
                // Set and activate the option
                static fn (Option $option) => $option->activate($value)
            )
        );

        return match (true) {
            $this->isStrict() &&
                $this->isMultiple() => array_map(
                    static fn (Option $option) => $option->getValue(),
                    $options
                ),

            $this->isMultiple() => Arr::wrap($value),

            $this->isStrict() => Arr::first($options)?->getValue(),

            default => $value
        };
    }

    /**
     * Get the options as an array.
     *
     * @return array<int,mixed>
     */
    public function optionsToArray()
    {
        return array_map(
            static fn (Option $option) => $option->toArray(),
            $this->getOptions()
        );
    }

    /**
     * Create a new option instance.
     *
     * @template TValue of \Honed\Refine\Option|scalar|null
     *
     * @param  TValue  $value
     * @param  string|null  $label
     * @return Option
     */
    protected static function newOption($value, $label = null)
    {
        return $value instanceof Option
            ? $value
            : Option::make($value, $label ?? (string) $value); // @phpstan-ignore cast.string
    }

    /**
     * Create options from a value.
     *
     * @template TValue of scalar|null|\Honed\Refine\Option
     *
     * @param  class-string<BackedEnum>|array<int|string,TValue>|Collection<int|string,TValue>  $options
     * @return array<int,Option>
     */
    protected function createOptions($options)
    {
        if ($options instanceof Collection) {
            $options = $options->all();
        }

        return match (true) {
            is_string($options) => $this->createEnumOptions($options),
            Arr::isAssoc($options) => $this->createAssociativeOptions($options),
            default => $this->createListOptions($options),
        };
    }

    /**
     * Create options from a backed enum.
     *
     * @param  class-string<BackedEnum>  $enum
     * @return array<int,Option>
     */
    protected function createEnumOptions($enum)
    {
        return array_map(
            static fn ($case) => Option::make(
                $case->value,
                $case instanceof HasLabel ? $case->getLabel() : Str::of($case->name)->snake(' ')->lower()->ucfirst()->toString()
            ),
            $enum::cases()
        );
    }

    /**
     * Create options from an associative array.
     *
     * @template TValue of \Honed\Refine\Option|scalar|null
     *
     * @param  array<int|string,TValue>  $options
     * @return array<int,Option>
     */
    protected function createAssociativeOptions($options)
    {
        return array_map(
            static fn ($value, $key) => static::newOption($value, $key), // @phpstan-ignore argument.type
            array_keys($options),
            array_values($options)
        );
    }

    /**
     * Create options from a list of values.
     *
     * @template TValue of scalar|\Honed\Refine\Option|null
     *
     * @param  array<int|string,TValue>  $options
     * @return array<int,Option>
     */
    protected function createListOptions($options)
    {
        /** @var array<int,Option> */
        return array_map(
            static fn ($value) => static::newOption($value),
            $options
        );
    }
}
