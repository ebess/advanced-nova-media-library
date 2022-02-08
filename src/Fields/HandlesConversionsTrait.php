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

    public function getConversionUrls(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        return [
            // original needed several purposes like cropping
            '__original__' => $media->getFullUrl(),
            'indexView' => $media->getFullUrl($this->meta['conversionOnIndexView'] ?? ''),
            'detailView' => $media->getFullUrl($this->meta['conversionOnDetailView'] ?? ''),
            'form' => $media->getFullUrl($this->meta['conversionOnForm'] ?? ''),
            'preview' => $media->getFullUrl($this->meta['conversionOnPreview'] ?? ''),
        ];
    }

    public function getTemporaryConversionUrls(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        return [
            // original needed several purposes like cropping
            '__original__' => $media->getTemporaryUrl($this->secureUntil),
            'indexView' => $media->getTemporaryUrl($this->secureUntil, $this->meta['conversionOnIndexView'] ?? ''),
            'detailView' => $media->getTemporaryUrl($this->secureUntil, $this->meta['conversionOnDetailView'] ?? ''),
            'form' => $media->getTemporaryUrl($this->secureUntil, $this->meta['conversionOnForm'] ?? ''),
            'preview' => $media->getTemporaryUrl($this->secureUntil, $this->meta['conversionOnPreview'] ?? ''),
        ];
    }
}
