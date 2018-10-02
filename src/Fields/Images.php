<?php

namespace Ebess\AdvancedNovaMediaLibrary\Fields;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Validator;

class Images extends Field
{
	public $component = 'advanced-media-library-field';

    private $singleImageRules = [];

	public function thumbnail(string $thumbnail): self
	{
		return $this->withMeta(compact('thumbnail'));
	}

	public function multiple(): self
	{
		return $this->withMeta(['multiple' => true]);
	}

	public function fullSize(): self
	{
		return $this->withMeta(['fullSize' => true]);
	}

	public function singleImageRules($singleImageRules): self
	{
		$this->singleImageRules = $singleImageRules;

		return $this;
	}

	/**
	 * @param HasMedia $model
	 */
	protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
	{
		$data = request($requestAttribute, []);

		collect($data)
			->filter(function ($value) {
				return $value instanceof UploadedFile;
			})
			->each(function($image) use ($requestAttribute) {
				Validator::make([$requestAttribute => $image], [
					$requestAttribute => array_merge(['image'], (array) $this->singleImageRules),
				])->validate();
			});

		Validator::make($data, $this->rules)->validate();

		$remainingIds = $this->removeDeletedImages($data, $model->getMedia($attribute));
		$newIds = $this->addNewImages($data, $model, $attribute);
		$this->setOrder($remainingIds->union($newIds)->sortKeys()->all());
	}

	private function setOrder($ids)
	{
		$mediaClass = config('medialibrary.media_model');
		$mediaClass::setNewOrder($ids);
	}

	private function addNewImages($data, HasMedia $model, string $collection): Collection
	{
		return collect($data)
			->filter(function ($value) {
				return $value instanceof UploadedFile;
			})->map(function (UploadedFile $file) use ($model, $collection) {
				return $model->addMedia($file)
					->toMediaCollection($collection)
					->getKey();
			});
	}

	private function removeDeletedImages($data, Collection $medias): Collection
	{
		$remainingIds = collect($data)->filter(function ($value) {
			return !$value instanceof UploadedFile;
		})->map(function ($value) {
			return (int)$value;
		});

		$medias->pluck('id')->diff($remainingIds)->each(function($id) use ($medias) {
			/** @var Media $media */
			if ($media = $medias->where('id', $id)->first()) {
				$media->delete();
			}
		});

		return $remainingIds;
	}

	/**
	 * @param HasMedia $resource
	 * @param null $attribute
	 */
	public function resolve($resource, $attribute = null)
	{
        $this->value = $resource->getMedia($attribute ?? $this->attribute)
            ->map(function(\Spatie\MediaLibrary\Models\Media $media) {
                $urls = [
                    'default' => $media->getFullUrl()
                ];

                if ($thumbnail = $this->meta['thumbnail'] ?? null) {
                    $urls[$thumbnail] = $media->getFullUrl($thumbnail);
                }

                return array_merge($media->toArray(), ['full_urls' => $urls]);
            })
        ;

		if ($data = $this->value->first()) {
			$thumbnailUrl = $data['full_urls'][$this->meta['thumbnail'] ?? 'default'];
			$this->withMeta(compact('thumbnailUrl'));
		}
	}
}
