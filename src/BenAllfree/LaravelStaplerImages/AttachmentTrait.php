<?php

namespace BenAllfree\LaravelStaplerImages;

trait AttachmentTrait
{
  public function hasSetMutator($key)
  {
    preg_match('/(.*)_(?:image_url|file_url)$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_name) = $matches;
      return $this->getAttribute($field_name."_id");
    }
    return parent::hasSetMutator($key);
  }
  
  public function setAttribute($key, $value)
  {
    preg_match('/(.*)_(image_url|file_url)$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_name, $url_type) = $matches;
      preg_match('/(.*)_url/', $url_type, $matches);
      list($match_data, $type_name) = $matches;
      switch($type_name)
      {
        case 'image':
          $i = Image::from_url($value);
          break;
        case 'url':
          $i = Attachment::from_url($value);
          break;
        default:
          throw new Exception("Unrecognized attachment type {$type_name}");
      }
      $field_name .= "_{$type_name}_id";
      return parent::setAttribute($field_name, $i->id);
    }
    return parent::setAttribute($key, $value);
  }
  
}