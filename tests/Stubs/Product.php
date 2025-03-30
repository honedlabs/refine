<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use SoftDeletes;
    // use Searchable;

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
    ];

    /**
     * Get the details for the product.
     */
    public function details(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
