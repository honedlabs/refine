<?php

declare(strict_types=1);

namespace Honed\Refine\Concerns;

trait HasQualifier
{
    /**
     * Whether to qualify against the builder.
     *
     * @var bool
     */
    protected $qualify = true;

    /**
     * Set whether to qualify against the builder.
     *
     * @param  bool  $qualify
     * @return $this
     */
    public function qualify($qualify = true)
    {
        $this->qualify = $qualify;

        return $this;
    }

    /**
     * Set whether to not qualify against the builder.
     *
     * @param  bool  $unqualify
     * @return $this
     */
    public function unqualify($unqualify = true)
    {
        return $this->qualify(! $unqualify);
    }

    /**
     * Determine if the instance should qualify against the builder.
     *
     * @return bool
     */
    public function isQualified()
    {
        return $this->qualify;
    }
}
