<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'file_name' => $this->file_name,
            'preview_url' => $this->getFullUrl()
        ];
    }
}
