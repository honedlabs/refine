<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

use Illuminate\Support\Str;

use function is_string;

trait HasQualifier
{
    /**
     * Whether to qualify against the builder.
     *
     * @var bool|string|null
     */
    protected $qualify;

    /**
     * Whether to qualify against the builder by default.
     *
     * @var bool
     */
    protected static $shouldQualify = false;

    /**
     * Set whether to qualify against the builder by default.
     *
     * @param  bool  $shouldQualify
     * @return void
     */
    public static function shouldQualify($shouldQualify = true)
    {
        static::$shouldQualify = $shouldQualify;
    }

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
        return $this->qualify ?? static::$shouldQualify;
    }

    /**
     * Determine if the instance should qualify against the builder.
     *
     * @return bool
     */
    public function qualifies()
    {
        return (bool) $this->getQualifier();
    }

    /**
     * Get the qualified name.
     *
     * @param  string  $column
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|null  $builder
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
