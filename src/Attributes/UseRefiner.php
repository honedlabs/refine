<?php

declare(strict_types=1);

namespace Honed\Refine\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class UseRefiner
{
    /**
     * Create a new attribute instance.
     *
     * @param  class-string<\Honed\Refine\Refine>  $refinerClass
     * @return void
     */
    public function __construct(
        public string $refinerClass
    ) {}
}
