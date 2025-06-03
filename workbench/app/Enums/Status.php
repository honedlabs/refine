<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum Status: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case ComingSoon = 'coming-soon';
}
