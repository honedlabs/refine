<?php

declare(strict_types=1);

namespace Honed\Refine\Enums;

enum SearchMode: string
{
    case Wildcard = 'wildcard';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case NaturalLanguage = 'natural_language';
    case Boolean = 'boolean';
}
