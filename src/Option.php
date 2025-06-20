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
     * @param  scalar|null  $value
     * @param  string|null  $label
     * @return static
     */
    public static function make($value, $label = null)
    {
        return resolve(static::class)
            ->value($value)
            ->label($label ?? (string) $value);
    }

    /**
     * Activate the option.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function activate($value)
    {
        return $this->active = match (true) {
            is_array($value) => in_array($this->getValue(), $value, true),
            default => $this->getValue() === $value,
        };
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'active' => $this->isActive(),
        ];
    }
}
