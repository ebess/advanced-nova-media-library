<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

class Images extends Media
{
    protected $defaultValidatorRules = ['image'];

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->croppable(config('nova-media-library.default-croppable', true));
    }

    /**
     * Do we deprecate this for SingleMediaRules?
     * @param $singleImageRules
     * @return Images
     */
    public function singleImageRules($singleImageRules): static
    {
        $this->singleMediaRules = $singleImageRules;

        return $this;
    }

    public function croppable(bool $croppable): static
    {
        return $this->withMeta(compact('croppable'));
    }

    public function croppingConfigs(array $configs): static
    {
        return $this->withMeta(['croppingConfigs' => $configs]);
    }

    public function showStatistics(bool $showStatistics = true): static
    {
        return $this->withMeta(compact('showStatistics'));
    }

    public function showDimensions(bool $showDimensions = true): static
    {
        return $this->showStatistics();
    }

    public function mustCrop(bool $mustCrop = true): static
    {
        return $this->withMeta(['mustCrop' => $mustCrop]);
    }
}
