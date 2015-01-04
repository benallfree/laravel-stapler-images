<?php
namespace BenAllfree\LaravelStaplerImages;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class Image  extends \Eloquent implements StaplerableInterface 
{
  use EloquentTrait;

  public static function from_url($url, $force_fetch=false)
  {
    if(!$force_fetch)
    {
      $i = Image::whereUrl($url)->first();
      if($i) return $i;
    }
    $i = new Image();
    $i->url = $url;
    $i->image = $url;
    $i->save();
    return $i;
  }
  
  public function __construct(array $attributes = array()) {
      $this->hasAttachedFile('image', [
          'styles' => self::styles()
      ]);

      parent::__construct($attributes);
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
    $styles = \Config::get('laravel-stapler-images::config.sizes');
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
