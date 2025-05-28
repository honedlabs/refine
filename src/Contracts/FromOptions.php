<?php

namespace Honed\Refine\Contracts;

interface FromOptions
{
    /**
     * Define the source you wish to create options from.
     * 
     * @return class-string<\BackedEnum>|array<int|string,scalar|null>|\Illuminate\Support\Collection<int|string,scalar|null>
     */
    public function optionsFrom();
}
