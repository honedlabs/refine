<?php

declare(strict_types=1);

return [
    Workbench\App\Providers\WorkbenchServiceProvider::class,
    Honed\Refine\RefineServiceProvider::class,
    Honed\Persist\PersistServiceProvider::class,
    Laravel\Scout\ScoutServiceProvider::class,
    Sti3bas\ScoutArray\ScoutArrayEngineServiceProvider::class,
];
