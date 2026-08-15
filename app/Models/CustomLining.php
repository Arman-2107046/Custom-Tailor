<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomLining extends Model
{
    protected $fillable = [
        'fabric_id',
        'custom_lining_fabric_id',
        'lining_type_id',
        'image',
        'layer_index',
        'is_default',
        'status',
    ];

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function customLiningFabric(): BelongsTo
    {
        return $this->belongsTo(CustomLiningFabric::class);
    }

    public function liningType(): BelongsTo
    {
        return $this->belongsTo(LiningType::class);
    }
}
