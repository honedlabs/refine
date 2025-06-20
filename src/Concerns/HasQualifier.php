<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

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
     * @param  bool|string  $qualify
     * @return $this
     */
    public function qualify($qualify = true)
    {
        $this->qualify = $qualify;

        return $this;
    }

    /**
     * Get the qualifier.
     *
     * @return bool|string
     */
    public function getQualifier()
    {
        return $this->qualify;
    }

    /**
     * Determine if the instance should qualify against the builder.
     *
     * @return bool
     */
    public function isQualifying()
    {
        return (bool) $this->getQualifier();
    }

    /**
     * Get the qualified name.
     *
     * @param  string  $column
     * @param  Builder<\Illuminate\Database\Eloquent\Model>|null  $builder
     * @return string
     */
    public function qualifyColumn($column, $builder = null)
    {
        $qualifier = $this->getQualifier();

        if (! $qualifier) {
            return $column;
        }

        if (is_string($qualifier) && ! Str::contains($column, '.')) {
            $column = Str::finish($qualifier, '.').$column;
        }

        return $builder
            ? $builder->qualifyColumn($column)
            : $column;
    }
}
