<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * @param  \Illuminate\Database\Eloquent\Model|class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected static function createBuilder(Model|string|Builder $query): Builder
    {
        if ($query instanceof Model) {
            return $query::query();
        }

        if (\is_string($query) && \class_exists($query)) {
            return $query::query();
        }

        if (! $query instanceof Builder) {
            throw new \InvalidArgumentException('Expected a model class name or a query instance.');
        }

        return $query;
    }
}
