<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use Illuminate\Contracts\Support\Arrayable;

trait HasOptions
{
    /**
     * @var array<int,\Honed\Refine\Filters\Concerns\Option>|null
     */
    protected $options;

    /**
     * @param  class-string<\BackedEnum>|iterable<mixed>  $options
     */
    public function options(string|iterable $options): static
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = match (true) {
            \is_string($options) && \is_a($options, \BackedEnum::class, true) => \array_map(
                fn ($case) => Option::make($case->value, $case->name),
                $options::cases()
            ),
            \array_is_list($o = type($options)->asArray()) => \array_map(
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
     * @return array<int,\Honed\Refine\Filters\Concerns\Option>
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    public function hasOptions(): bool
    {
        return ! \is_null($this->options) && \count($this->options) > 0;
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     */
    public function enum(string $enum): static
    {
        $this->options($enum);

        return $this;
    }
}
