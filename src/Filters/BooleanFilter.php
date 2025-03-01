<?php

declare(strict_types=1);

namespace Honed\Refine\Filters;

class BooleanFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->type('boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function handle($builder, $value, $property)
    {
        $column = $builder->qualifyColumn($property);

        $builder->where(
            column: $column,
            operator: self::Is,
            value: $value,
            boolean: 'and'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return (bool) $this->getValue();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function getRefiningValue($request)
    {
        return $request->safeBoolean($this->getParameter());
    }
}
