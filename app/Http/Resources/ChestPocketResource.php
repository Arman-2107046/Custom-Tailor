<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChestPocketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'chest_pocket_name' => $this->chest_pocket_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'diagram' => $this->diagram,

            'buttons' => ChestPocketButtonResource::collection(
                $this->whenLoaded('chestPocketButtons')
            ),
        ];
    }
}
