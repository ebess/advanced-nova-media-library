# Laravel Advanced Nova Media Library

Manage images of [spatie's media library package](https://github.com/spatie/laravel-medialibrary). Upload multiple
images and order them by drag and drop.

##### Table of Contents  
* [Install](#install)  
* [Model media configuration](#model-media-configuration)  
* [Generic file management](#generic-file-management)  
* [Single image upload](#single-image-upload)  
* [Multiple image upload](#multiple-image-upload)  
* [Names of uploaded images](#names-of-uploaded-images)  
* [Custom properties](#custom-properties)  
* [Media Field (Video)](#media-field-video)  

## Install
```bash
composer require ebess/advanced-nova-media-library
```

## Model media configuration

Let's assume you configured your model to use the media library like following:
```php
use Spatie\MediaLibrary\Models\Media;

public function registerMediaConversions(Media $media = null)
{
    $this->addMediaConversion('thumb')
        ->width(130)
        ->height(130);
}

public function registerMediaCollections()
{
    $this->addMediaCollection('main')->singleFile();
    $this->addMediaCollection('my_multi_collection');
}
```

## Generic file management

![Generic file management](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/file-management.png)

In order to be able to upload and handle generic files just go ahead and use the `Files` field.

```php
use Ebess\AdvancedNovaMediaLibrary\Fields\Files;

Files::make('Single file', 'one_file'),
Files::make('Multiple files', 'multiple_files')->multiple(),
```

## Single image upload

![Single image upload](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/single-image.png)

```php
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;

public function fields(Request $request)
{
    return [
        Images::make('Main image', 'main') // second parameter is the media collection name
            ->thumbnail('thumb') // conversion used to display the image
            ->rules('required'), // validation rules
    ];
}
```

## Multiple image upload

If you enable the multiple upload ability, you can **order the images via drag & drop**.

![Multiple image upload](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/multiple-images.png)

```php
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;

public function fields(Request $request)
{
    return [
        Images::make('Images', 'my_multi_collection') // second parameter is the media collection name
            ->conversion('medium-size') // conversion used to display the "original" image
            ->conversionOnView('thumb') // conversion used on the model's view
            ->thumbnail('thumb') // conversion used to display the image on the model's index page
            ->multiple() // enable upload of multiple images - also ordering
            ->fullSize() // full size column
            ->rules('required', 'size:3') // validation rules for the collection of images
            // validation rules for the collection of images
            ->singleImageRules('dimensions:min_width=100'),
    ];
}
```

## Names of uploaded images

The default filename of the new uploaded file is the original filename. You can change this with the help of the function `setFileName`, which takes a callback function as the only param. This callback function has three params: `$originalFilename` (the original filename like `Fotolia 4711.jpg`), `$extension` (file extension like `jpg`), `$model` (the current model). Here are just 2 examples of what you can do:

```php
// Set the filename to the MD5 Hash of original filename
Images::make('Image 1', 'img1')
    ->setFileName(function($originalFilename, $extension, $model){
        return md5($originalFilename) . '.' . $extension;
    });

// Set the filename to the model name
Images::make('Image 2', 'img2')
    ->setFileName(function($originalFilename, $extension, $model){
        return str_slug($model->name) . '.' . $extension;
    });
```

By default, the "name" field on the Media object is set to the original filename without the extension. To change this, you can use the `setName` function. Like `setFileName` above, it takes a callback function as the only param. This callback function has two params: `$originalFilename` and `$model`.

```php
Images::make('Image 1', 'img1')
    ->setName(function($originalFilename, $model){
        return md5($originalFilename);
    });
```

## Responsive images

If you want to use responsive image functionality from the [Spatie MediaLibrary](https://docs.spatie.be/laravel-medialibrary/v7/responsive-images/getting-started-with-responsive-images), you can use the `withResponsiveImages()` function on the model.

```php
Images::make('Image 1', 'img1')
    ->withResponsiveImages();

```

## Custom properties

![Custom properties](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/custom-properties.gif)

```php
Images::make('Gallery')
    ->customPropertiesFields([
        Boolean::make('Active'),
        Markdown::make('Description'),
    ]);
    
Files::make('Multiple files', 'multiple_files')->multiple()
    ->customPropertiesFields([
        Boolean::make('Active'),
        Markdown::make('Description'),
    ]);
    
// custom properties without user input
Files::make('Multiple files', 'multiple_files')->multiple()
    ->customProperties([
        'foo' => auth()->user()->foo,
        'bar' => $api->getNeededData(),
    ]);
```

## Media Field (Video)

In order to handle videos with thumbnails you need to use the `Media` field instead of `Images`. This way you are able to upload videos as well.

```php
use Ebess\AdvancedNovaMediaLibrary\Fields\Media;

class Category extends Resource
{
    public function fields(Request $request)
    {
        Media::make('Gallery') // media handles videos
            ->thumbnail('thumb')
            ->singleMediaRules('max:5000'); // max 5000kb
    }
}

// ..

class YourModel extends Model implements HasMedia
{
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->extractVideoFrameAtSecond(1);
    }
}
```

# Credits

* [nova media library](https://github.com/jameslkingsley/nova-media-library)
