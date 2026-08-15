<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fabric extends Model
{
    protected $fillable = [
        'name',
        'price',
        'image',
        'is_default',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function sleeves()
    {
        return $this->hasMany(Sleeve::class);
    }

    public function sidePockets()
    {
        return $this->hasMany(SidePocket::class);
    }

    public function chestPockets()
    {
        return $this->hasMany(ChestPocket::class);
    }

    public function body()
    {
        return $this->hasMany(Body::class);
    }

    public function lapels()
    {
        return $this->hasMany(Lapel::class);
    }


    public function bodyButtons()
    {
        return $this->hasMany(BodyButton::class);
    }

    public function customLinings()
    {
        return $this->hasMany(CustomLining::class);
    }

    public function defaultLinings(): HasMany
    {
        return $this->hasMany(DefaultLining::class);
    }
}
