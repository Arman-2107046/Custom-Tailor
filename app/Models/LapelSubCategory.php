<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LapelSubCategory extends Model
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
     * Lapels using this sub category
     */
    public function lapels(): HasMany
    {
        return $this->hasMany(Lapel::class);
    }
}
