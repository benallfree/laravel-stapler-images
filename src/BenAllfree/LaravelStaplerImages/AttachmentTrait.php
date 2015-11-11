<?php

namespace BenAllfree\LaravelStaplerImages;

trait AttachmentTrait
{
  public function hasSetMutator($key)
  {
    preg_match('/(.*)_(image|file)_path$/', $key, $matches);
    if(count($matches)>0)
    {
      return true;
    }
    return parent::hasSetMutator($key);
  }
  
  public function setAttribute($key, $value)
  {
    preg_match('/(.*)_(image|file)_path$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_prefix, $field_type) = $matches;
      if(!preg_match('/^https?:/', $value)) // If this is a local path
      {
        $try = [
          $value,
          config('laravel-stapler.images.storage_path')."/{$value}",
          storage_path($value),
          app_path($value),
        ];
        foreach($try as $file_path)
        {
          if(file_exists($file_path))
          {
            $value = $file_path;
            break;
          }
        }
        if($value != $file_path)
        {
          throw new Exception("File path {$value} not found.");
        }
      }
      switch($field_type)
      {
        case 'image':
          $i = Image::from_url($value);
          break;
        case 'file':
          $i = Attachment::from_url($value);
          break;
        default:
          throw new Exception("Unrecognized attachment type {$type_name}");
      }
      $field_name = "{$field_prefix}_{$field_type}_id";
      return parent::setAttribute($field_name, $i->id);
    }
    return parent::setAttribute($key, $value);
  }
  
  public function hasGetMutator($key)
  {
    preg_match('/(.*)_(?:image|file)$/', $key, $matches);
    if(count($matches)>0)
    {
      return true;
    }
    return parent::hasGetMutator($key);
  }
  
  public function mutateAttribute($key, $value)
  {
    preg_match('/(.*)_(?:image|file)$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_type) = $matches;
      $field_name = "{$field_type}_id";
      switch($field_type)
      {
        case 'image':
          return $this->belongsTo(Image::class, $field_name);
          break;
        case 'file':
          return $this->belongsTo(Attachment::class, $field_name);
          break;
        default:
          throw new Exception("Unrecognized attachment type {$field_type}");
      }
    }
    return parent::mutateAttribute($key, $value);
  }  
}