<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'body_name' => $this->body_name,
            'layering_index' => $this->layering_index,
            'image' => $this->image,
            'body_diagram' => $this->body_diagram,

            'sleeves' => SleeveResource::collection(
                $this->whenLoaded('sleeves')
            ),

            'body_buttons' => BodyButtonResource::collection(
                $this->whenLoaded('bodyButtons')
            ),

            'lapels' => LapelResource::collection(
                $this->whenLoaded('lapels')
            ),

            'side_pockets' => SidePocketResource::collection(
                $this->whenLoaded('sidePockets')
            ),

            'chest_pockets' => ChestPocketResource::collection(
                $this->whenLoaded('chestPockets')
            ),

            'linings' => LiningResource::collection(
                $this->whenLoaded('linings')
            ),
        ];
    }
}
