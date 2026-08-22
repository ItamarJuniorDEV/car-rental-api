<?php

namespace App\Models;

use Database\Factories\LineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Line extends Model
{
    /** @use HasFactory<LineFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'name',
        'image',
        'door_count',
        'seats',
        'air_bag',
        'abs',
    ];

    protected $casts = [
        'air_bag' => 'boolean',
        'abs' => 'boolean',
    ];

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return HasMany<Car, $this> */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }
}
