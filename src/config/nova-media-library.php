<?php

return [
    'enable-existing-media' => false,

    /**
     * Set a default conversion to use by collection.
     * Can be set for all view with a string or by view with an array.
     * Possible array keys are: index, detail, form, preview and fallback.
     *
     * i.e.
     * ['image' => 'thumb']
     * or
     * ['image' => [
     *  'detail' => 'full',
     *  'fallback' => 'thumb',
     * ]
     */
    'collections-default-conversions' => [],
];
