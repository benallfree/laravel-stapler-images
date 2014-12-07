<?php
namespace BenAllfree\LaravelStaplerImages;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class Image  extends \Eloquent implements StaplerableInterface 
{
  use EloquentTrait;

  public static function from_url($url)
  {
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
    return \Config::get('laravel-stapler-images::config.sizes');
  }
}

Image::saving(function($obj) {
  $obj->sizes_md5 = Image::style_md5();
});