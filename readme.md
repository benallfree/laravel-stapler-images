# Laravel Stapler Images

This Laravel 5 package builds upon [codesleeve/laravel-stapler](https://github.com/CodeSleeve/laravel-stapler) and takes a different approach to attachment storage by storing attachments in a single table. Besides this normalized approach to attachment storage, it also handles images and image sizes.

There are several benefits to storing your attachments in a single table:

* Easier mainteannce - no additional migrations needed when new attachments are added
* Utilities and Laravel commands to handle attachments do not need to know specific column mames
* Normalized data is more organized and searchable
* Avoids duplication of images

## Setup

Install

    composer require benallfree/laravel-stapler-images

Add the service providers to `config/app.php`

    BenAllfree\LaravelStaplerImages\LaravelStaplerImagesServiceProvider::class,
    Codesleeve\LaravelStapler\Providers\L5ServiceProvider::class,

Optionally add an alias for the `\Image` and `\Attachment` classes in `config/app.php`

    'Image' => BenAllfree\LaravelStaplerImages\Image::class,
    'Attachment' => BenAllfree\LaravelStaplerImages\Attachment::class,

Publish the config

    php artisan vendor:publish

Take a look at the config files in `config/laravel-stapler`. If you're not familiar with the config files, see the [basic Stapler config docs](https://github.com/CodeSleeve/stapler/blob/master/docs/configuration.md). We add `images.php` where you can control settings for this package. In particular, if you want to adjust the name of the table and the sizes of images created, you can do it here.

We like this setting for `config/laravel-stapler/filesystem.php`

    'url' => '/i/:id_partition/:style/:filename',

Don't forget to migrate:

    php artisan migrate

## Usage

Super easy. Let's add an avatar to our `User` model.

First, create a migration. In this case, let's do a simple `belongsTo` relationship.

    Schema::table('users', function (Blueprint $table) {
      $table->integer('avatar_image_id');
    });

We named it `avatar_image_id` because we want the field to be treated as an `BenAllfree\LaravelStaplerImages\Image` object so image processing happens. This gives us extra features like processing various image sizes. If we didn't need that, we could have named it `avatar_file_id` and it would be a `BenAllfree\LaravelStaplerImages\Attachment` instead.

Now, add it to the User table


    use BenAllfree\LaravelStaplerImages\AttachmentTrait;
    
    class User
    {
      use AttachmentTrait;
    }

Great. Now we can rock and roll. Saving will generate and save all the images on the spot.

    // Create an image
    $url = "http://www.gravatar.com/avatar/71137e6e1c94b72f162da3262b700017.png";
    $user->avatar_image_path = $url;
    $user->save();

The `$url` can be a file path too.

Next, we can recall a processed image URL. The MIME type is always `image/jpg` for these.

    // Use an image
    echo $user->avatar->url('thumb');

The following sizes exist by default:

    'large' => '640x640#',
    'featured' => '585x585#',
    'medium' => '400x400#',
    'thumb' => '180x180#',
    'admin' => '100x100#',
    'tiny' => '75x75#',

## Magic Getters and Setters

Given a database field `<name>_file_id`

`<name>_file` - A getter that returns an `Attachment` object for the given underlying ID
`<name>_file_path()` - A setter mutator that accepts a file path or URL and creates an `Attachment` object from it. If it can't find the file with the path specified, it will look in the `storage_path` config setting, then in `storage_path()`, then in `root_path()`.
  
Likewise, a database field `<name>_image_id` will do the same thing for `Image`, except it will add image processing when the objects are created.

`<name>_image` - A getter that returns an `Image` object for the given underlying ID
`<name>_image_path()` - A setter mutator that accepts a file path or URL and creates an `Attachment` object from it. If it can't find the file with the path specified, it will look in the `storage_path` config setting, then in `storage_path()`, then in `root_path()`.
  
`Image` is NOT a subclass of `Attachment`, so these should not be used interchangeably. 
  
## Caching and Performance

By default, `Image::from_url($url)` will check `$url` against the `original_file_name` column in the images table and will only fetch the image the first time it has to. If you want to force it, use `from_url($url, true)`.

## Custom Image Sizes

No problem, just go into `config/laravel-stapler-images.php` and make whatever sizes you want.

## Reprocessing Images

If you change sizes of images, you will need to reprocess existing images.

    php artisan images:reprocess

## Securing Images

If you want your images to be served from within secure routes instead of directly available from the `public` folder, make these modifications:

Create the following file:

    /storage/uploads/.gitkeep

In `config/laravel-stapler/filesystem.php` to change where the images are stored (outside the webroot):

	'path' => ':app_root/storage/uploads:url',

Then, create a route like this and add whatever security you need:

    Route::get('/images/{id}/{size}', function($id,$size) {
      $image = \Image::find($id);
      if(!$image)
      {
        App::abort(404);
      }
  
      $response = Response::make(
         File::get($image->image->path($size)), 
         200
      );
      $response->header(
        'Content-type',
        'image/jpeg'
      );
      return $response;
    });

## Integrating with Laravel Administrator

Do you love [Laravel Administrator](https://github.com/FrozenNode/Laravel-Administrator) as much as I do? Sweet. Here's how you do it.

In `config/laravel-stapler/images.php`, there is an `admin_path` that should be configured. You might like the default, and if you do, then you need to make sure the directory exists (someone fix that please).

Then, in `config/administrator/<your model>.php`, configure your model file as follows:
  
    <?php
    
    return array(
      
      'title' => 'Users',
      
      'single' => 'User',
      
      'model' => 'App\User',
      
      /**
       * The display columns
       */
      'columns' => array(
        'id',
        'avatar_id' => array(
          'title' => 'Avatar',
          'output'=>function($id) {
            if(!$id) return '';
            $i = \Image::find($id);
            return "<img src='{$i->url('admin')}?r={$i->updated_at->timestamp}' width=50/>";
          },
        ),    
      ),
      
      /**
       * The editable fields
       */
      'edit_fields' => array(
        'avatar_image_laravel_administrator_fname'=>[
          'title'=>'Avatar',
          'type'=>'image',
          'location'=>config('laravel-stapler.images.admin_path').'/',
        ]
        
      ),
      
    );

That's it! Now you have images from Laravel Administrator!

## Workarounds and bugfixes

The core `codesleeve/stapler` package has a couple bugs that may be important to you. We have included patch files to fix the bugs and will keep these up to date with the required versions of `stapler`.

After you run a `composer update`, you'll need to re-apply these patches. Here is a shell script to help out. I like this better than composer's hooks because sometimes `artisan` won't run if ServiceProvider dependencies are missing.

    #!/bin/bash
    rm -rf vendor/codesleeve/stapler
    composer $1
    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-rename_bugfix.patch
    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-curl-open-basedir-fix.patch
    php artisan clear-compiled
    php artisan optimize

### curl/open_basedir bug

If you are on shared hosting, you may find that `open_basedir` is set and `curl` will fail to fetch URLs. To fix that, we have included a patch file with this package.

    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-curl-open-basedir-fix.patch

### rename bug

`codesleeve/stapler` renames files as part of processing URL downloads and using temporary files. If your temporary files are stored on a different volume, there is a [known PHP issue](https://bugs.php.net/bug.php?id=50676) that will cause a Laravel exception. To fix it, `@rename` should be used:

    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-rename_bugfix.patch


 
