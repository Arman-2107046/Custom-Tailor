<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BodyType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'diagram',
    ];

    public function bodies(): HasMany
    {
        return $this->hasMany(Body::class);
    }
    public function bodyButtons(): HasMany
    {
        return $this->hasMany(BodyButton::class);
    }

    public function defaultLinings(): HasMany
    {
        return $this->hasMany(DefaultLining::class);
    }
}
