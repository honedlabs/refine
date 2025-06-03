<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Concerns\IsActive;
use Honed\Core\Primitive;

use function in_array;
use function is_array;

class Option extends Primitive
{
    use HasLabel;
    use HasValue;
    use IsActive;

    /**
     * Create a new option.
     *
     * @param  mixed  $value
     * @param  string|null  $label
     * @return static
     */
    public static function make($value, $label = null)
    {
        return resolve(static::class)
            ->value($value)
            ->label($label ?? (string) $value); // @phpstan-ignore-line
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($named = [], $typed = [])
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'active' => $this->isActive(),
        ];
    }

    /**
     * Activate the option.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function activate($value)
    {
        $optionValue = $this->getValue();

        $active = match (true) {
            is_array($value) => in_array($optionValue, $value, true),
            default => $optionValue === $value,
        };

        $this->active = $active;

        return $active;
    }
}
