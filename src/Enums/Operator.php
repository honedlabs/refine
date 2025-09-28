<?php

declare(strict_types=1);

namespace Honed\Refine\Enums;

enum Operator: string
{
    case Is = 'is';
    case IsNot = 'is_not';
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqual = 'lte';
    case Contains = 'like';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case IsNull = 'is_null';

    /**
     * Get the primitive operator.
     *
     * @return string
     */
    public function operator()
    {
        return match ($this) {
            self::Is => '=',
            self::IsNot => '!=',
            self::GreaterThan => '>',
            self::GreaterThanOrEqual => '>=',
            self::LessThan => '<',
            self::LessThanOrEqual => '<=',
            self::Contains,
            self::StartsWith,
            self::EndsWith => 'LIKE',
            self::IsNull => '=',
        };
    }
}
