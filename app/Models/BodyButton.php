<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyButton extends Model
{
    protected $fillable = [
        'body_type_id',
        'button_image_id',
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

    /**
     * Body Type
     *
     * Example:
     * SB1, SB2, DB2, DB4, etc.
     */
    public function bodyType(): BelongsTo
    {
        return $this->belongsTo(BodyType::class);
    }

    /**
     * Button Image
     */
    public function buttonImage(): BelongsTo
    {
        return $this->belongsTo(ButtonImage::class);
    }
}
