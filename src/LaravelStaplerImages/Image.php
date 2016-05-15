<?php
namespace BenAllfree\LaravelStaplerImages;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class Image  extends Model implements StaplerableInterface
{
  use EloquentTrait;

  public static function from_url($url, $force_fetch=false)
  {
    if(!$force_fetch)
    {
      $i = Image::whereOriginalFileName($url)->first();
      if($i) return $i;
    }
    $i = new Image();
    $i->original_file_name = $url;
    $i->att = $url;
    $i->save();
    return $i;
  }

  function getTable()
  {
    return config('laravel-stapler.images.table_name');
  }

  public function __construct(array $attributes = array()) {
    $this->hasAttachedFile('att', [
        'styles' => self::styles()
    ]);

    parent::__construct($attributes);
  }

  function url($size='thumb')
  {
    return $this->att->url($size);
  }

  function path($size='')
  {
    return $this->att->path($size);
  }

  public function should_reprocess()
  {
    return $this->sizes_md5 != self::style_md5();
  }

  public static function style_md5()
  {
    return md5(json_encode(self::styles()));
  }

  public static function styles()
  {
    $styles = config('laravel-stapler.images.sizes');
    if(!$styles || count($styles)==0)
    {
      throw new \Exception("No sizes defined for Image class. Are you sure you registered the service provider?");
    }
    return $styles;
  }
}

Image::saving(function($obj) {
  $obj->sizes_md5 = Image::style_md5();
});
