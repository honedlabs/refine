<?php

declare(strict_types=1);

namespace Honed\Refine;

use Honed\Core\Concerns\Allowable;
use Honed\Core\Concerns\HasAlias;
use Honed\Core\Concerns\HasAttribute;
use Honed\Core\Concerns\HasLabel;
use Honed\Core\Concerns\HasMeta;
use Honed\Core\Concerns\HasType;
use Honed\Core\Concerns\HasValue;
use Honed\Core\Primitive;
use Honed\Refine\Contracts\Refines;

/**
 * @extends Primitive<string, mixed>
 */
abstract class Refiner extends Primitive implements Refines
{
    use Allowable;
    use HasAlias;
    use HasAttribute;
    use HasLabel;
    use HasMeta;
    use HasType;
    use HasValue;

    public function __construct(string $attribute, ?string $label = null)
    {
        $this->attribute($attribute);
        $this->label($label ?? $this->makeLabel($attribute));
        $this->setUp();
    }

    public static function make(string $attribute, ?string $label = null): static
    {
        return resolve(static::class, \compact('attribute', 'label'));
    }

    public function getParameter(): string
    {
        return $this->getAlias()
            ?? str($this->getAttribute())
                ->afterLast('.')
                ->value();
    }

    /**
     * Determine if the refiner is currently being applied.
     */
    abstract public function isActive(): bool;

    public function toArray(): array
    {
        return [
            'name' => $this->getParameter(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'active' => $this->isActive(),
            'meta' => $this->getMeta(),
        ];
    }
}
