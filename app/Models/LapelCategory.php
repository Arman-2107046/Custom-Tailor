<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LapelCategory extends Model
{
    protected $fillable = [
        'name',
        'diagram',
        'status',
        'is_default',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Sub categories under this lapel category
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(
            LapelSubCategory::class
        );
    }

    /**
     * Lapels using this category
     */
    public function lapel(): HasMany
    {
        return $this->hasMany(Lapel::class);
    }
}
