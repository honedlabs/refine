<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Workbench\App\Enums\Status;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\Factories\HasFactory<
     */
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
    ];

    public function toSearchableArray()
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
