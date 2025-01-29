<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

use Honed\Refine\Concerns\HasCallback;
use Illuminate\Database\Eloquent\Builder;

class CallbackSort extends Sort
{
    use HasCallback;

    public function handle(Builder $builder, string $direction, string $property): void
    {
        $this->evaluate(
            value: $this->getCallback(),
            named: [
                'builder' => $builder,
                'direction' => $direction,
                'property' => $property,
                'attribute' => $property,
            ],
            typed: [
                Builder::class => $builder,
            ],
        );
    }
}
