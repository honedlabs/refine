<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

use function is_string;

trait HasQualifier
{
    /**
     * Whether to qualify against the builder.
     *
     * @var bool|string
     */
    protected $qualify = false;

    /**
     * Set whether to qualify against the builder.
     *
     * @return $this
     */
    public function qualify(bool|string $qualify = true): static
    {
        $this->qualify = $qualify;

        return $this;
    }

    /**
     * Set whether to not qualify against the builder.
     *
     * @return $this
     */
    public function dontQualify(bool $value = true): static
    {
        return $this->qualify(! $value);
    }

    /**
     * Get the qualifier.
     */
    public function getQualifier(): bool|string
    {
        return $this->qualify;
    }

    /**
     * Determine if the instance should qualify against the builder.
     */
    public function isQualifying(): bool
    {
        return (bool) $this->getQualifier();
    }

    /**
     * Get the qualified name.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>|null  $builder
     * @return ($column is string ? string : Expression)
     */
    public function qualifyColumn(string|Expression $column, ?Builder $builder = null): string|Expression
    {
        $qualifier = $this->getQualifier();

        if (! $qualifier || $column instanceof Expression) {
            return $column;
        }

        if (is_string($qualifier) && ! Str::contains($column, '.')) {
            $column = Str::finish($qualifier, '.').$column;
        }

        return $builder ? $builder->qualifyColumn($column) : $column;
    }
}
