<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * @mixin Media
 */
trait HandlesCustomPropertiesTrait
{
	protected $customPropertiesFields = [];
	protected $customProperties = [];

	public function customPropertiesFields(array $customPropertiesFields): self
	{
		$this->customPropertiesFields = collect($customPropertiesFields);

		return $this->withMeta(compact('customPropertiesFields'));
	}

	public function customProperties(array $customProperties): self
	{
		$this->customProperties = $customProperties;

		return $this;
	}

	private function fillCustomPropertiesFromRequest(NovaRequest $request, HasMedia $model, string $collection)
	{
		$mediaItems = $model->getMedia($collection);
		$items = $request->{$collection};

		// do not handle files as custom properties on files are not supported yet
		if ($items instanceof FileBag) {
			return;
		}

		collect($items)
			->reject(function ($value) {
				return $value instanceof UploadedFile || $value instanceof FileBag;
			})
			->each(function (int $id, int $index) use ($request, $mediaItems, $collection) {
				if (!$media = $mediaItems->where('id', $id)->first()) {
					return;
				}

				$this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);
			});
	}

	/**
	 * @param \Spatie\MediaLibrary\Models\Media $media
	 */
	private function fillMediaCustomPropertiesFromRequest(NovaRequest $request, $media, int $index, string $collection)
	{
		// prevent overriding the custom properties set by other processes like generating convesions
		$media->refresh();

		/** @var Field $field */
		foreach ($this->customPropertiesFields as $field) {
			$field->fillInto(
				$request,
				$media,
				"custom_properties->{$field->attribute}",
				"{$collection}-custom-properties.{$index}.{$field->attribute}"
			);
		}

		$media->save();
	}
}
