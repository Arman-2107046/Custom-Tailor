<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SidePocketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'side_pocket_name' => $this->side_pocket_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'diagram' => $this->diagram,

            'buttons' => SidePocketButtonResource::collection(
                $this->whenLoaded('sidePocketButtons')
            ),
        ];
    }
}
