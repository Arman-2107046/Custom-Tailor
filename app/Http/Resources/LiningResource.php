<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiningResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'lining_name' => $this->lining_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'lining_diagram' => $this->lining_diagram,
        ];
    }
}
