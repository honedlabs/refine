<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use Illuminate\Support\Collection;

trait HasOptions
{
    /**
     * @var array<int,\Honed\Refine\Filters\Concerns\Option>|null
     */
    protected $options;

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
            \is_string($options) && \is_a($options, \BackedEnum::class, true) => \array_map(
                fn ($case) => Option::make($case->value, $case->name),
                $options::cases()
            ),
            \array_is_list($o = $options) => \array_map(
                fn ($value) => $value instanceof Option ? $value : Option::make($value),
                $o
            ),
            default => \array_map(
                fn ($value, $label) => Option::make($value, $label),
                \array_keys($o),
                $o,
            ),
        };

        return $this;
    }

    /**
     * Get the options.
     *
     * @return array<int,\Honed\Refine\Filters\Concerns\Option>
     */
    public function getOptions()
    {
        return $this->options ?? [];
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
     * Get the options as an array.
     *
     * @return array<int,mixed>
     */
    public function optionsToArray()
    {
        return \array_map(
            fn (Option $option) => $option->toArray(),
            $this->getOptions()
        );
    }
}
