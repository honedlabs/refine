<?php

declare(strict_types=1);

namespace Honed\Refine\Sorts;

use Honed\Refine\Concerns\HasCallback;
use Illuminate\Database\Eloquent\Builder;

class CallbackSort extends Sort
{
    use HasCallback;

    /**
     * Handle the sort.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  string  $direction
     * @param  string  $property
     * @return void
     */
    public function handle($builder, $direction, $property)
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
