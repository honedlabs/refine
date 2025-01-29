<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

use Honed\Refine\Concerns\HasCallback;
use Illuminate\Database\Eloquent\Builder;

class CallbackFilter extends Filter
{
    use HasCallback;

    public function handle(Builder $builder, mixed $value, string $property): void
    {
        $this->evaluate(
            value: $this->getCallback(),
            named: [
                'builder' => $builder,
                'value' => $value,
                'property' => $property,
                'attribute' => $property,
            ],
            typed: [
                Builder::class => $builder,
            ],
        );
    }
}
