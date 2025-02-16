<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Concerns\IsActive;
use Honed\Core\Primitive;

/**
 * @extends Primitive<string, mixed>
 */
class Option extends Primitive
{
    use HasLabel;
    use HasValue;
    use IsActive;

    /**
     * Create a new option.
     * 
     * @param  string|int|float|bool  $value
     * @return $this
     */
    public static function make(mixed $value, ?string $label = null): static
    {
        return resolve(static::class)
            ->value($value)
            ->label($label ?? (string) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'active' => $this->isActive(),
        ];
    }
}
