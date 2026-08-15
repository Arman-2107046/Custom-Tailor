<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SidePocketButtonResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'button_name' => $this->button_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
        ];
    }
}

