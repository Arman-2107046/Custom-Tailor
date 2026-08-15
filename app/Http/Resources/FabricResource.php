<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FabricResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fabric_name' => $this->fabric_name,
            'image' => $this->image,

            'bodies' => BodyResource::collection(
                $this->whenLoaded('bodies')
            ),
        ];
    }
}
