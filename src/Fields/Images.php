<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Validator;

class Images extends Media
{

    protected $defaultValidatorRules = ['image'];

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
}
