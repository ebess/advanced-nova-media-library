<?php

namespace Sietzekeuning\AdvancedNovaMediaLibrary\Fields;

/**
 * Class Files
 *
 * @package Sietzekeuning\AdvancedNovaMediaLibrary\Fields
 */
class Files extends Images
{
    protected $defaultValidatorRules = [];

    public $meta = ['type' => 'file'];

	public function __construct($name, $attribute = null, $resolveCallback = null)
	{
		parent::__construct($name, $attribute, $resolveCallback);

		$this->hideFromIndex();
	}
}
