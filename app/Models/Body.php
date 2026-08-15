<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Body extends Model
{
    protected $fillable = [
        'fabric_id',
        'body_type_id',
        'image',
        'layer_index',
        'is_default',
        'status',
    ];

    protected $casts = [
        'layer_index' => 'integer',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function bodyType(): BelongsTo
    {
        return $this->belongsTo(BodyType::class);
    }

    public function lapels(): HasMany
    {
        return $this->hasMany(Lapel::class);
    }

    public function bodyButtons(): HasMany
    {
        return $this->hasMany(BodyButton::class);
    }

}
