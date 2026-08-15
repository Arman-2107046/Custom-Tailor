<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ButtonImage extends Model
{
    protected $fillable = [
        'name',
        'diagram',
    ];

    /**
     * Body buttons using this button image.
     */
    public function bodyButtons(): HasMany
    {
        return $this->hasMany(BodyButton::class);
    }
}
