<?php

declare(strict_types=1);

namespace Honed\Refine\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }
}
