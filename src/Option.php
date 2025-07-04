<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\CanBeActive;
use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Primitive;

use function in_array;

class Option extends Primitive
{
    use CanBeActive;
    use HasLabel;
    use HasValue;

    /**
     * Create a new option.
     *
     * @param  scalar|null  $value
     */
    public static function make(mixed $value, ?string $label = null): static
    {
        return resolve(static::class)
            ->value($value)
            ->label($label ?? (string) $value);
    }

    /**
     * Activate the option.
     */
    public function activate(mixed $value): bool
    {
        return $this->active = in_array(
            $this->getValue(), (array) $value, true
        );
    }

    /**
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'active' => $this->isActive(),
        ];
    }
}
