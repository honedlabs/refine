<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasBuilderInstance
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|null
     */
    protected $builder;

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @return $this
     */
    public function builder(Builder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function getBuilder(): Builder
    {
        if (\is_null($this->builder)) {
            throw new \RuntimeException('Builder instance has not been set.');
        }

        return $this->builder;
    }
}
