# Laravel Stapler Images

This Laravel 4 package sovles a difficult problem of managing image attachments for models. This picks up where `codesleeve/laravel-stapler` leaves off, adding database normalization and more complete resizing and maintenance features.

## Setup

At minium
    php artisan migrate:publish benallfree/laravel-stapler-images
    php artisan migrate

You may also like to add an alias to `app.php`:

    'Image' => 'BenAllfree\LaravelStaplerImages\Image',

## Usage

Super easy.

    class User
    {
      function avatar()
      {
        return $this->belongsTo('Image', 'avatar_id');
      }
    }
    
    // Create an image
    $url = "http://www.gravatar.com/avatar/71137e6e1c94b72f162da3262b700017.png";
    $user->avatar = Image::from_url($url);
    $user->save();
    
    // Use an image
    echo $user->avatar->image->url('thumb');

The following sizes exist by default:

    'large' => '640x640#',
    'featured' => '585x585#',
    'medium' => '400x400#',
    'thumb' => '180x180#',
    'admin' => '100x100#',
    'tiny' => '75x75#',

## Custom Image Sizes

To customize image sizes:

    php artisan config:publish benallfree/laravel-stapler-images

Then edit `app/config/packages/benallfree/laravel-stapler-images/config.php`

## Reprocessing Images

If you change sizes of images, you will need to reprocess existing images.

    php artisan images:reprocess

## Securing Images

If you want your images to be served from within secure routes instead of directly available from the `public` folder, make these modifications:

Create the following file:

    app/storage/uploads/.gitkeep

In `config/packages/codesleeve/laravel-stapler/filesystem.php`:

	'path' => ':app_root/app/storage/uploads:url',

Then, create a route like this and add whatever security you need:

    Route::get('/images/{id}/{size}', function($id,$size) {
      $image = Image::find($id);
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

## Workarounds and bugfixes

The core `codesleeve/stapler` package has a couple bugs that may be important to you. We have included patch files to fix the bugs and will keep these up to date with the required versions of `stapler`.

For both of the below bugfixes, we recommend adding them to your `composer.json` to automate the updates:

    "pre-update-cmd": [
      "rm -rf vendor/codesleeve/stapler"
    ],
    "post-update-cmd": [
      "rm -rf vendor/codesleeve/stapler/.git",
      "git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-rename_bugfix.patch"    
      "git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-curl-open-basedir-fix.patch"    
    ],

### curl/open_basedir bug

If you are on shared hosting, you may find that `open_basedir` is set and `curl` will fail to fetch URLs. To fix that, we have included a patch file with this package.

    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-curl-open-basedir-fix.patch

### rename bug

`codesleeve/stapler` renames files as part of processing URL downlaods and using temporary files. If your temporary files are stored on a different volume, there is a [known PHP issue](https://bugs.php.net/bug.php?id=50676) that will cause a Laravel exception. To fix it, `@rename` should be used:

    git apply vendor/benallfree/laravel-stapler-images/codesleeve-stapler-rename_bugfix.patch


 