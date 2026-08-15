<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomLiningFabric extends Model
{
    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    public function customLinings()
    {
        return $this->hasMany(CustomLining::class);
    }
}
