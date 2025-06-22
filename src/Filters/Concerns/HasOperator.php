<?php

declare(strict_types=1);

namespace Honed\Refine\Filters\Concerns;

use Honed\Refine\Enums\Operator;

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
     * @param  string|Operator  $operator
     * @return $this
     */
    public function operator($operator)
    {
        if ($operator instanceof Operator) {
            $this->operator = $operator->operator();
        } else {
            $this->operator = mb_strtoupper($operator, 'UTF8');
        }

        return $this;
    }

    /**
     * Set the operator to be '>'
     *
     * @return $this
     */
    public function greaterThan()
    {
        return $this->operator(Operator::GreaterThan->operator());
    }

    /**
     * Set the operator to be '>'
     *
     * @return $this
     */
    public function gt()
    {
        return $this->greaterThan();
    }

    /**
     * Set the operator to be '>='
     *
     * @return $this
     */
    public function greaterThanOrEqualTo()
    {
        return $this->operator(Operator::GreaterThanOrEqual->operator());
    }

    /**
     * Set the operator to be '>='
     *
     * @return $this
     */
    public function gte()
    {
        return $this->greaterThanOrEqualTo();
    }

    /**
     * Set the operator to be '<'
     *
     * @return $this
     */
    public function lessThan()
    {
        return $this->operator(Operator::LessThan->operator());
    }

    /**
     * Set the operator to be '<'
     *
     * @return $this
     */
    public function lt()
    {
        return $this->lessThan();
    }

    /**
     * Set the operator to be '<='
     *
     * @return $this
     */
    public function lessThanOrEqualTo()
    {
        return $this->operator(Operator::LessThanOrEqual->operator());
    }

    /**
     * Set the operator to be '<='
     *
     * @return $this
     */
    public function lte()
    {
        return $this->lessThanOrEqualTo();
    }

    /**
     * Set the operator to be '!='
     *
     * @return $this
     */
    public function notEqualTo()
    {
        return $this->operator(Operator::IsNot->operator());
    }

    /**
     * Set the operator to be '!='
     *
     * @return $this
     */
    public function neq()
    {
        return $this->notEqualTo();
    }

    /**
     * Set the operator to be '='
     *
     * @return $this
     */
    public function equals()
    {
        return $this->operator(Operator::Is->operator());
    }

    /**
     * Set the operator to be '='
     *
     * @return $this
     */
    public function eq()
    {
        return $this->equals();
    }

    /**
     * Set the operator to be 'LIKE'
     *
     * @return $this
     */
    public function like()
    {
        return $this->operator(Operator::Contains->operator());
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
