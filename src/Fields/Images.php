<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

class Images extends Media
{
    protected $defaultValidatorRules = ['image'];

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * Do we deprecate this for SingleMediaRules?
     * @param $singleImageRules
     * @return Images
     */
    public function singleImageRules($singleImageRules): self
    {
        $this->singleMediaRules = $singleImageRules;

        return $this;
    }

    public function showStatistics(bool $showStatistics = true): self
    {
        return $this->withMeta(compact('showStatistics'));
    }

    public function showDimensions(bool $showDimensions = true): self
    {
        return $this->showStatistics();
    }
}
