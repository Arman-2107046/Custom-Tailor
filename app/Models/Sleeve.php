<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sleeve extends Model
{
    protected $fillable = [
        'fabric_id',
        'sleeve_type_id',
        'name',
        'image',
        'diagram',
        'layer_index',
        'is_default',
        'status',
    ];


    protected $casts = [
        'layer_index' => 'integer',
        'is_default'  => 'boolean',
        'status'      => 'boolean',
    ];


    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }


    public function sleeveType(): BelongsTo
    {
        return $this->belongsTo(SleeveType::class);
    }
}
