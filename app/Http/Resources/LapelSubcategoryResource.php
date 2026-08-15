<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LapelSubcategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subcategory_name' => $this->subcategory_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'lapel_diagram' => $this->lapel_diagram,

        ];
    }
}
