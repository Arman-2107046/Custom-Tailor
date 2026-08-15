<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChestPocketType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'diagram',
    ];

    public function chestPockets(): HasMany
    {
        return $this->hasMany(ChestPocket::class);
    }
}
