<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SidePocketButton extends Model
{
    use HasFactory;

    protected $fillable = [
        'side_pocket_id',
        'button_name',
        'layering_index',
        'image',
    ];

    // Side Pocket Button → Side Pocket
    public function sidePocket()
    {
        return $this->belongsTo(SidePocket::class);
    }
}
