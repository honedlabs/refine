<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Honed\Refine\Concerns\HasRefiner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Workbench\App\Enums\Status;
use Workbench\Database\Factories\ProductFactory;

class Product extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Workbench\Database\Factories\ProductFactory>
     */
    use HasFactory;

    /**
     * @use \Honed\Refine\Concerns\HasRefiner<\Workbench\App\Refiners\ProductRefiner>
     */
    use HasRefiner;

    use Searchable;
    use SoftDeletes;

    /**
     * The factory for the model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    protected static $factory = ProductFactory::class;

    /**
     * The attributes that cannot be mass assigned.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, class-string>
     */
    protected $casts = [
        'status' => Status::class,
    ];

    /**
     * Get the user that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the search data for the model using Laravel Scout.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
