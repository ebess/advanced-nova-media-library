<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Resources;

use Ebess\AdvancedNovaMediaLibrary\Fields\HandlesConversionsTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    use HandlesConversionsTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * This is incompatible with following settings on the Field.
         * - conversionOnIndexView
         * - conversionOnDetailView
         * - conversionOnForm
         * - conversionOnPreview
         * - serializeMediaUsing
         *
         */
        return array_merge($this->resource->toArray(), ['__media_urls__' => $this->getConversionUrls($this->resource)]);
    }
}
