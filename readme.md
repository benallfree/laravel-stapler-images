# Laravel Stapler Images

This Laravel 5 package builds upon [codesleeve/laravel-stapler](https://github.com/CodeSleeve/laravel-stapler) and takes a different approach to attachment storage by storing attachments in a single table. Besides this normalized approach to attachment storage, it also handles images and image sizes.

There are several benefits to storing your attachments in a single table:

* Easier mainteannce - no additional migrations needed when new attachments are added
* Utilities and Laravel commands to handle attachments do not need to know specific column names
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

Take a look at the config files in `config/laravel-stapler`. If you're not familiar with the config files, see the [basic Stapler config docs](https://github.com/CodeSleeve/stapler/blob/master/docs/configuration.md). I add `images.php` where you can control settings for this package. In particular, if you want to adjust the name of the table and the sizes of images created, you can do it here.

I like this setting for `config/laravel-stapler/filesystem.php`

    'url' => '/i/:id_partition/:style/:filename',

Don't forget to migrate:

    php artisan migrate

## Basic Usage

Super easy. Let's add an avatar to our `User` model.

First, create a migration. In this case, let's do a simple `belongsTo` relationship.

    Schema::table('users', function (Blueprint $table) {
      $table->integer('avatar_image_id');
    });

I named it `avatar_image_id` because we want the field to be treated as an `BenAllfree\LaravelStaplerImages\Image` object so image processing happens. This gives us extra features like processing various image sizes. If we didn't need that, we could have named it `avatar_file_id` and it would be a `BenAllfree\LaravelStaplerImages\Attachment` instead.

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
    echo $user->avatar_image->url('thumb');

The following sizes exist by default:

    'large' => '640x640#',
    'featured' => '585x585#',
    'medium' => '400x400#',
    'thumb' => '180x180#',
    'admin' => '100x100#',
    'tiny' => '75x75#',

## Forms and Input

Given a field named `avatar_image` like above...

### In the view

```
{!! Form::open(['method'=>'post', 'files'=>true]) !!}
{!! Form::file('avatar_image') !!}
{!! Form::submit('Update') !!}
{!! Form ::close() !!}
```

### In the controller

```
function do_postback(Request $r)
{
  $u = Auth::user();
  $u->update($r->input());
  $u->update($r->files()); // This is the important one
}
```

## Magic Getters and Setters

Given a database field `<name>_file_id`

`<name>_file` - A getter that returns an `Attachment` object for the given underlying ID
`<name>_file_path()` - A setter mutator that accepts a file path or URL and creates an `Attachment` object from it. If it can't find the file with the path specified, it will look in the `la_path` config setting, then in `la_path()`, then in `root_path()`.
  
Likewise, a database field `<name>_image_id` will do the same thing for `Image`, except it will add image processing when the objects are created.

`<name>_image` - A getter that returns an `Image` object for the given underlying ID
`<name>_image_path()` - A setter mutator that accepts a file path or URL and creates an `Attachment` object from it. If it can't find the file with the path specified, it will look in the `la_path` config setting, then in `la_path()`, then in `root_path()`.
  
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

## Advanced Usage (Working directly with attachments)

If you have a pivot table or some other need to work directly with attachments:

    $image = Image::from_url($url);
    $att = Attachment::from_url($url);

## Integrating with Laravel Administrator

Do you love [Laravel Administrator](https://github.com/FrozenNode/Laravel-Administrator) as much as I do? Sweet. Here's how you do it.

First, familiarize yourself with the `[location](http://administrator.frozennode.com/docs/field-type-image)` attribute of upload fields in Laravel Administrator.

### Step 1: Choose ONE location where Laravel Administrator will upload your files.

In `config/laravel-stapler/images.php`, there is an `la_path` that can be configured. The default is fine, but if you want to change it you may. Use the same location for ALL models in Laravel Administrator. Laravel Stapler Images will look in this config path for any uploads being saved. I suggest adding a `.gitkeep` to the path.

### Step 2: Configure your Laravel Administrator model, being careful to use the `config()` path you chose in Step 1.

Configure `config/administrator/<your model>.php` as follows:
  
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
        'avatar_image_id' => array(
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
        'avatar_image_la'=>[
          'title'=>'Avatar',
          'type'=>'image',
          'location'=>config('laravel-stapler.images.la_path').'/',
        ]
        
      ),
      
    );

### Step 3: Add extra JSON attributes to your Eloquent model via `$appends`.

Recall our User model above contained an `avatar_image_id` field, and that we can use `$user->avatar_image` to access it.

    class User
    {
      use AttachmentTrait;
    }

To make sure Laravel Administrator sees it, we must modify the model just a bit:

    class User
    {
      use AttachmentTrait;
      
      protected $appends = ['avatar_image_la'];
    }

The `_la` suffix indicates that this is a Laravel Administrator file attachment field. 

That's it! Now you have images from Laravel Administrator!

## Workarounds and bugfixes

The core `codesleeve/stapler` package has a couple bugs that may be important to you. To use my forked version of Stapler with the bugs fixed, do the following:

In your composer.json:

    "repositories": [
      {
          "type": "vcs",
          "url": "git@github.com:benallfree/stapler.git"
      }
    "require": {
        "codesleeve/stapler": "dev-master as 1.2.0",
    },

### Hashtag and long URL name fixes

Two problems:

1. If a URL containing a # (hash) is fetched, it will fail to use the proper file extension, this creating MIME type problems.
2. If a URL is too long, it will faile to write to the file system. I fixed this by using shorter names.

### curl/open_basedir bug

If you are on shared hosting, you may find that `open_basedir` is set and `curl` will fail to fetch URLs. To fix that, we have included a patch file with this package.

### rename bug

`codesleeve/stapler` renames files as part of processing URL downloads and using temporary files. If your temporary files are stored on a different volume, there is a [known PHP issue](https://bugs.php.net/bug.php?id=50676) that will cause a Laravel exception. To fix it, `@rename` should be used.
