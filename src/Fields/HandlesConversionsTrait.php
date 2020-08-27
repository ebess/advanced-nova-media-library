<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

/**
 * @mixin Media
 */
trait HandlesConversionsTrait
{
    public function conversionOnIndexView(string $conversionOnIndexView): self
    {
        return $this->withMeta(compact('conversionOnIndexView'));
    }

    public function conversionOnDetailView(string $conversionOnDetailView): self
    {
        return $this->withMeta(compact('conversionOnDetailView'));
    }

    public function conversionOnForm(string $conversionOnForm): self
    {
        return $this->withMeta(compact('conversionOnForm'));
    }

    public function conversionOnPreview(string $conversionOnPreview): self
    {
        return $this->withMeta(compact('conversionOnPreview'));
    }

    public function getDefaultConversionForCollection(string $collection, $view = null)
    {
        $defaultConversions = config("nova-media-library.collections-default-conversions.$collection");
        if (is_string($defaultConversions)){
            return $defaultConversions;
        } elseif (is_array($defaultConversions)){
            return $defaultConversions[$view] ?? ($defaultConversions['fallback'] ?? '');
        }
        return '';
    }

    public function getConversionUrls(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        $collection = $media->collection_name;
        return [
            // original needed several purposes like cropping
            '__original__' => $media->getFullUrl(),
            'indexView' => $media->getFullUrl($this->meta['conversionOnIndexView'] ??
                $this->getDefaultConversionForCollection($collection, 'index')
            ),
            'detailView' => $media->getFullUrl($this->meta['conversionOnDetailView'] ??
                $this->getDefaultConversionForCollection($collection, 'detail')
            ),
            'form' => $media->getFullUrl($this->meta['conversionOnForm'] ??
                $this->getDefaultConversionForCollection($collection, 'form')
            ),
            'preview' => $media->getFullUrl($this->meta['conversionOnPreview'] ??
                $this->getDefaultConversionForCollection($collection, 'preview')
            ),
        ];
    }
}
