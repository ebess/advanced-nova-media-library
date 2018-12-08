<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

/**
 * Class Files
 *
 * @package Ebess\AdvancedNovaMediaLibrary\Fields
 */
class Files extends Images
{
    protected $defaultValidatorRules = [];

    public $meta = ['type' => 'file'];
}