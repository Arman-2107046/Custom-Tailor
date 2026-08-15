<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiningType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'diagram',
    ];

    /**
     * Custom linings belonging to this lining type.
     */
    public function customLinings(): HasMany
    {
        return $this->hasMany(CustomLining::class);
    }
    public function defaultLinings(): HasMany
    {
        return $this->hasMany(DefaultLining::class);
    }
}
