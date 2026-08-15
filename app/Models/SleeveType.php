<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SleeveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'diagram',
    ];


    public function sleeves(): HasMany
    {
        return $this->hasMany(Sleeve::class);
    }
}
