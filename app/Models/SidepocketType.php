<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidePocketType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'diagram',
    ];

    public function sidePockets(): HasMany
    {
        return $this->hasMany(SidePocket::class);
    }
}
