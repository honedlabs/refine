<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Feature\Concerns;

use Honed\Refine\Refine;
use Workbench\App\Models\Product;

beforeEach(function () {
    $this->encoded = 'search+term';

    $this->decoded = 'search term';

    $this->refine = Refine::make()->for(Product::class);
});

it('sets search term', function () {
    expect($this->refine)
        ->getSearchTerm()->toBeNull()
        ->setSearchTerm($this->encoded)->toBeNull()
        ->getSearchTerm()->toBe($this->decoded);
});

it('encodes search term', function () {
    expect($this->refine)
        ->encodeSearchTerm($this->decoded)->toBe($this->encoded)
        ->encodeSearchTerm(null)->toBeNull();
});

it('decodes search term', function () {
    expect($this->refine)
        ->decodeSearchTerm($this->encoded)->toBe($this->decoded)
        ->decodeSearchTerm(null)->toBeNull();
});