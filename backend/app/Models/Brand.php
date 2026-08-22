<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'image'];

    /** @return HasMany<Line, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(Line::class);
    }
}
