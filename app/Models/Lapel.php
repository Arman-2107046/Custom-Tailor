<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lapel extends Model
{
    protected $fillable = [

        'fabric_id',
        'body_id',

        'lapel_category_id',
        'lapel_subcategory_id',

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
     * Fabric relationship
     */
    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }



    /**
     * Body relationship
     */
    public function body(): BelongsTo
    {
        return $this->belongsTo(Body::class);
    }



    /**
     * Lapel Category relationship
     */
    public function lapelCategory(): BelongsTo
    {
        return $this->belongsTo(
            LapelCategory::class,
            'lapel_category_id'
        );
    }



    /**
     * Lapel Subcategory relationship
     */
    public function lapelSubcategory(): BelongsTo
    {
        return $this->belongsTo(
            LapelSubCategory::class,
            'lapel_subcategory_id'
        );
    }
}
