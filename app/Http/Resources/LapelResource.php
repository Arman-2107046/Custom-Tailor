<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LapelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'lapel_name' => $this->lapel_name,
            'layering_index' => $this->layering_index,
            'main_lapel_diagram' => $this->main_lapel_diagram,

            'subcategories' => LapelSubcategoryResource::collection(
                $this->whenLoaded('subcategories')
            ),
        ];
    }
}

