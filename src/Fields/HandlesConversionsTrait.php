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

    private function setDefaultConversion(string $collection)
    {
        $defaults = [
            'conversionOnIndexView'  => $this->getDefaultConversionForCollection($collection, 'index') ?? '',
            'conversionOnDetailView' => $this->getDefaultConversionForCollection($collection, 'detail') ?? '',
            'conversionOnForm'       => $this->getDefaultConversionForCollection($collection, 'form') ?? '',
            'conversionOnPreview'    => $this->getDefaultConversionForCollection($collection, 'preview') ?? '',
        ];
        if(method_exists($this, 'withMeta')) {
            $this->withMeta($defaults);
        } else {
            $this->meta = array_merge($this->meta ?? [], $defaults);
        }
    }

    public function getConversionUrls(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        $this->setDefaultConversion($media->collection_name);
        return [
            // original needed several purposes like cropping
            '__original__' => $media->getFullUrl(),
            'indexView' => $media->getFullUrl($this->meta['conversionOnIndexView'] ?? ''),
            'detailView' => $media->getFullUrl($this->meta['conversionOnDetailView'] ?? ''),
            'form' => $media->getFullUrl($this->meta['conversionOnForm']  ?? ''),
            'preview' => $media->getFullUrl($this->meta['conversionOnPreview'] ?? ''),
        ];
    }
}
