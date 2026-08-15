<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChestPocketButton extends Model
{
    use HasFactory;

    protected $fillable = [
        'chest_pocket_id',
        'button_name',
        'layering_index',
        'image',
    ];

    // Chest Pocket Button → Chest Pocket
    public function chestPocket()
    {
        return $this->belongsTo(ChestPocket::class);
    }
}
