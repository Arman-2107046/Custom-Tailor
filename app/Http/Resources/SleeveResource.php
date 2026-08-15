<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SleeveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sleeve_name' => $this->sleeve_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'sleeve_diagram' => $this->sleeve_diagram,
        ];
    }
}
