<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

trait HasOperator
{
    /**
     * The operator to use.
     *
     * @var string
     */
    protected $operator = '=';

    /**
     * Set the operator to use for the filter.
     *
     * @param  string  $operator
     * @return $this
     */
    public function operator($operator)
    {
        $this->operator = mb_strtoupper($operator, 'UTF8');

        return $this;
    }

    /**
     * Set the operator to be '>'
     *
     * @return $this
     */
    public function greaterThan()
    {
        return $this->operator('>');
    }

    /**
     * Set the operator to be '>'
     *
     * @return $this
     */
    public function gt()
    {
        return $this->operator('>');
    }

    /**
     * Set the operator to be '>='
     *
     * @return $this
     */
    public function greaterThanOrEqualTo()
    {
        return $this->operator('>=');
    }

    /**
     * Set the operator to be '>='
     *
     * @return $this
     */
    public function gte()
    {
        return $this->operator('>=');
    }

    /**
     * Set the operator to be '<'
     *
     * @return $this
     */
    public function lessThan()
    {
        return $this->operator('<');
    }

    /**
     * Set the operator to be '<'
     *
     * @return $this
     */
    public function lt()
    {
        return $this->operator('<');
    }

    /**
     * Set the operator to be '<='
     *
     * @return $this
     */
    public function lessThanOrEqualTo()
    {
        return $this->operator('<=');
    }

    /**
     * Set the operator to be '<='
     *
     * @return $this
     */
    public function lte()
    {
        return $this->operator('<=');
    }

    /**
     * Set the operator to be '!='
     *
     * @return $this
     */
    public function notEqualTo()
    {
        return $this->operator('!=');
    }

    /**
     * Set the operator to be '!='
     *
     * @return $this
     */
    public function neq()
    {
        return $this->operator('!=');
    }

    /**
     * Set the operator to be '='
     *
     * @return $this
     */
    public function equals()
    {
        return $this->operator('=');
    }

    /**
     * Set the operator to be '='
     *
     * @return $this
     */
    public function eq()
    {
        return $this->operator('=');
    }

    /**
     * Set the operator to be 'LIKE'
     *
     * @return $this
     */
    public function like()
    {
        return $this->operator('LIKE');
    }

    /**
     * Get the operator to use for the filter.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
