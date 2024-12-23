# Laravel Advanced Nova Media Library

Manage images of [spatie's media library package](https://github.com/spatie/laravel-medialibrary). Upload multiple
images and order them by drag and drop.

##### Table of Contents  
* [Examples](#examples)  
* [Install](#install)  
* [Model media configuration](#model-media-configuration)  
* [Generic file management](#generic-file-management)  
* [Single image upload](#single-image-upload)  
* [Multiple image upload](#multiple-image-upload)  
* [Selecting existing media](#selecting-existing-media)  
* [Names of uploaded images](#names-of-uploaded-images)  
* [Image cropping](#image-cropping)
* [Custom properties](#custom-properties)
* [Custom headers](#custom-headers)
* [Media Field (Video)](#media-field-video)  
* [Change log](#change-log)  

## Examples
![Cropping](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/cropping.gif)
![Single image upload](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/single-image.png)
![Multiple image upload](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/multiple-images.png)
![Custom properties](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/custom-properties.gif)
![Generic file management](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/file-management.png)

## Install
```bash
composer require ebess/advanced-nova-media-library
```

```bash
php artisan vendor:publish --tag=nova-media-library
```

## Model media configuration

Let's assume you configured your model to use the media library like following:
```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function registerMediaConversions(Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(130)
        ->height(130);
}

public function registerMediaCollections(): void
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
Files::make('Multiple files', 'multiple_files'),
```

## Single image upload

![Single image upload](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/single-image.png)

```php
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;

public function fields(Request $request)
{
    return [
        Images::make('Main image', 'main') // second parameter is the media collection name
            ->conversionOnIndexView('thumb') // conversion used to display the image
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
            ->conversionOnPreview('medium-size') // conversion used to display the "original" image
            ->conversionOnDetailView('thumb') // conversion used on the model's view
            ->conversionOnIndexView('thumb') // conversion used to display the image on the model's index page
            ->conversionOnForm('thumb') // conversion used to display the image on the model's form
            ->fullSize() // full size column
            ->rules('required', 'size:3') // validation rules for the collection of images
            // validation rules for the collection of images
            ->singleImageRules('dimensions:min_width=100'),
    ];
}
```

## Selecting existing media

![Selecting existing media](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/existing-media.png)
![Selecting existing media 2](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/existing-media-2.png)

If you upload the same media files to multiple models and you do not want to select it from the file system
all over again, use this feature. Selecting an already existing media will **copy it**.

**Attention**: This feature will expose an endpoint to every user of your application to search existing media. 
If your media upload / custom properties on the media models are confidential, **do not enable this feature!** 

* Publish the config files if you did not yet
```bash
artisan vendor:publish --tag=nova-media-library
```
* Enable this feature in config file *config/nova-media-library*
```php
return [
    'enable-existing-media' => true,
];
```
* Enable the selection of existing media field
```php
Images::make('Image')->enableExistingMedia(),
```

**Note**: This feature does not support temporary URLs.

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

## Image cropping

![Cropping](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/cropping.gif)

By default you are able to crop / rotate images by clicking the scissors in the left bottom corner on the edit view. 
The [vue-js-clipper](https://github.com/timtnleeProject/vuejs-clipper) is used for this purpose. The cropping feature is 
limited to mime type of `image/jpg`, `image/jpeg` and `image/png`.

**Important:** By cropping an existing image the original media model is deleted and replaced by the cropped image. 
All custom properties are copied form the old to the new model.

To disable this feature use the `croppable` method:
```php
Images::make('Gallery')->croppable(false);
```

You can set all configurations like ratio e.g. as following: 
```php
Images::make('Gallery')->croppingConfigs(['aspectRatio' => 4/3]);
```
Available cropping configuration, see https://github.com/timtnleeProject/vuejs-clipper#clipper-basic.

It is possible to enforce cropping on upload, for example to ensure the image has the set aspect ratio:
```php
Images::make('Gallery')->mustCrop();
```

### Disabling cropping by default

By default, the cropping feature is enabled. To disable it by default for all images set `default-croppable` in `config/nova-media-library.php` to `false`:
```php
return [
    'default-croppable' => false,
];
```

## Custom properties

![Custom properties](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/custom-properties.gif)

```php
Images::make('Gallery')
    ->customPropertiesFields([
        Boolean::make('Active'),
        Markdown::make('Description'),
    ]);
    
Files::make('Multiple files', 'multiple_files')
    ->customPropertiesFields([
        Boolean::make('Active'),
        Markdown::make('Description'),
    ]);
    
// custom properties without user input
Files::make('Multiple files', 'multiple_files')
    ->customProperties([
        'foo' => auth()->user()->foo,
        'bar' => $api->getNeededData(),
    ]);
```

## Show image statistics *(size, dimensions, type)*

![Image statistics](https://raw.githubusercontent.com/ebess/advanced-nova-media-library/master/docs/show-statistics.png)

```php
Images::make('Gallery')
    ->showStatistics();
```

## Custom headers

```php
Images::make('Gallery')
    ->customHeaders([
        'header-name' => 'header-value', 
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
            ->conversionOnIndexView('thumb')
            ->singleMediaRules('max:5000'); // max 5000kb
    }
}

// ..

class YourModel extends Model implements HasMedia
{
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->extractVideoFrameAtSecond(1);
    }
}
```

## Temporary Urls

If you are using Amazon S3 to store your media, you will need to use the `temporary` function on your field to generate
a temporary signed URL. This function expects a valid Carbon instance that will specify when the URL should expire.

```
Images::make('Image 1', 'img1')
    ->temporary(now()->addMinutes(5))

Files::make('Multiple files', 'multiple_files')
    ->temporary(now()->addMinutes(10),
```

**Note**: This feature does not work with the existing media feature. 

# Credits

* [nova media library](https://github.com/jameslkingsley/nova-media-library)

# Alternatives

* [dmitrybubyakin/nova-medialibrary-field](https://github.com/dmitrybubyakin/nova-medialibrary-field)

# Change log

## v4.0.2 - 2022-04-26
- Fix ratio for cropping in Nova 4. Config from `Images::( ... )->croppingConfigs()` are now passed along to the `stencil-props` property of the cropper. See [cropper docs](https://norserium.github.io/vue-advanced-cropper/components/rectangle-stencil.html#props) for more details on available props.

## v4.0.1 - 2022-04-20
- Fix details component
- Fix layout inconsistencies 

## v4.0.0 - 2022-04-18
- Upgrade to support Laravel Nova 4
- Breaks compatibility with Laravel Nova 1,2 and 3. For those nova versions  use `v3.*`
- Replaced [vuejs-clipper](https://www.npmjs.com/package/vuejs-clipper) with [vue-advanced-cropper](https://www.npmjs.com/package/vue-advanced-cropper) for vue3 support

Full change log in [PR #317](https://github.com/ebess/advanced-nova-media-library/pull/317)

# How to contribute

- You need to have Nova installed of course in your Laravel app
- Work directly in the package in the `vendor` directory (webpack needs Nova to be installed)
- Then from the `vendor/xxx/advanced-nova-media-library` folder:
    - Use a least `nvm use 14`
    - `yarn install` 
    - `yarn run watch` 
    - Work hard 🤘
    - `yarn npm production` when job is finished
    - Make a PR
