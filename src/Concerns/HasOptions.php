<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Honed\Refine\Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasOptions
{
    /**
     * The available options.
     *
     * @var array<int,\Honed\Refine\Option>
     */
    protected $options = [];

    /**
     * Whether to restrict options to only those provided.
     *
     * @var bool|null
     */
    protected $strict;

    /**
     * Whether to accept multiple values.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Set the options for the filter.
     *
     * @param  class-string<\BackedEnum>|array<int,mixed>|Collection<int,mixed>  $options
     * @return $this
     */
    public function options($options)
    {
        if ($options instanceof Collection) {
            $options = $options->all();
        }

        $this->options = match (true) {
            \is_string($options) => $this->optionsEnumerated($options),

            Arr::isAssoc($options) => $this->optionsAssociative($options),

            default => $this->optionsList($options),
        };

        return $this;
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
     * @param  bool  $strict
     * @return $this
     */
    public function lax($strict = false)
    {
        return $this->strict($strict);
    }

    /**
     * Allow multiple options to be used.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Create options from an enum.
     *
     * @param  class-string<\BackedEnum>  $enum
     * @return $this
     */
    public function enum($enum)
    {
        $this->options($enum);

        return $this;
    }

    /**
     * Determine if the filter has options.
     *
     * @return bool
     */
    public function hasOptions()
    {
        return filled($this->options);
    }

    /**
     * Get the options.
     *
     * @return array<int,\Honed\Refine\Option>
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Determine if only the options provided are allowed.
     *
     * @return bool
     */
    public function isStrict()
    {
        if (isset($this->strict)) {
            return $this->strict;
        }

        return $this->fallbackStrict();
    }

    /**
     * Determine if only the options provided are allowed.
     *
     * @return bool
     */
    public function fallbackStrict()
    {
        return (bool) config('refine.strict', false);
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
     * Get the options as an array.
     *
     * @return array<int,mixed>
     */
    public function optionsToArray()
    {
        return \array_map(
            static fn (Option $option) => $option->toArray(),
            $this->getOptions()
        );
    }

    /**
     * Create options from an enum.
     *
     * @param  class-string<\BackedEnum>  $enum
     * @return array<int,\Honed\Refine\Option>
     */
    public function optionsEnumerated($enum)
    {
        return \array_map(
            static fn ($case) => Option::make($case->value, $case->name),
            $enum::cases()
        );
    }

    /**
     * Create options from an associative array.
     *
     * @param  array<int,mixed>  $options
     * @return array<int,\Honed\Refine\Option>
     */
    public function optionsAssociative($options)
    {
        return \array_map(
            static fn ($value, $key) => Option::make($value, \strval($key)), // @phpstan-ignore-line
            \array_keys($options),
            \array_values($options)
        );
    }

    /**
     * Create options from a list.
     *
     * @param  array<int,mixed>  $options
     * @return array<int,\Honed\Refine\Option>
     */
    public function optionsList($options)
    {
        return \array_map(
            static fn ($value) => Option::make($value, \strval($value)), // @phpstan-ignore-line
            $options
        );
    }

    /**
     * Activate the options and return the valid options.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function activateOptions($value)
    {
        $options = collect($this->getOptions())
            ->filter(static fn (Option $option) => $option
                ->active(static::shouldActivate($option, $value))
                ->isActive()
            )->values();

        return match (true) {
            $this->isStrict() && $this->isMultiple() => $options->map->getValue()->all(),
            $this->isMultiple() => Arr::wrap($value),
            $this->isStrict() => $options->first()?->getValue(),
            default => $value
        };
    }

    /**
     * Determine if the option should be activated.
     *
     * @param  \Honed\Refine\Option  $option
     * @param  mixed  $value
     * @return bool
     */
    public static function shouldActivate($option, $value)
    {
        $optionValue = $option->getValue();

        if (\is_array($value)) {
            return \in_array($optionValue, $value, true);
        }

        return $optionValue === $value;
    }
}
