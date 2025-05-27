<?php

declare(strict_types=1);

namespace Honed\Refine\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Refiner
{
    /**
     * Create a new attribute instance.
     *
     * @param  class-string<\Honed\Refine\Refine>  $refiner
     * @return void
     */
    public function __construct(
        public string $refiner
    ) {}

    /**
     * Get the refine class.
     *
     * @return class-string<\Honed\Refine\Refine>
     */
    public function getRefiner()
    {
        return $this->refiner;
    }
}
