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
    preg_match('/(.*)_(image|file)(_la)?$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_prefix, $field_type) = $matches;
      $la_mode = count($matches)==4;
      $field_name = "{$field_prefix}_{$field_type}_id";
      if(!$value)
      {
        return parent::setAttribute($field_name, null);
      }
      if(!preg_match('/^https?:/', $value)) // If this is a local path
      {
        $try = [
          $value,
          config('laravel-stapler.images.la_path')."/{$value}",
          storage_path($value),
          app_path($value),
        ];
        foreach($try as $file_path)
        {
          if(file_exists($file_path) && is_file($file_path))
          {
            $value = $file_path;
            break;
          }
        }
        if($value != $file_path)
        {
          if($la_mode)
          {
            return $this->getAttribute($field_name);
          } else {
            throw new \Exception("File path to save {$value} not found.");
          }
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
          throw new \Exception("Unrecognized attachment type {$field_type}");
      }
      if($la_mode)
      {
        copy($i->path('admin'), $value);
      }
      return parent::setAttribute($field_name, $i->id);
    }
    return parent::setAttribute($key, $value);
  }
  
  public function hasGetMutator($key)
  {
    preg_match('/(.*)_(?:image|file)(?:_la)?$/', $key, $matches);
    if(count($matches)>0)
    {
      return true;
    }
    return parent::hasGetMutator($key);
  }
  
  public function mutateAttribute($key, $value)
  {
    preg_match('/(.*)_(image|file)(_la)?$/', $key, $matches);
    if(count($matches)>0)
    {
      list($match_data, $field_name_prefix, $field_type) = $matches;
      $la_mode = count($matches)==4;
      $field_name = "{$field_name_prefix}_{$field_type}_id";
      if(!$this->$field_name) return null;
      switch($field_type)
      {
        case 'image':
          $obj = Image::find($this->$field_name);
          break;
        case 'file':
          $obj = Attachment::find($this->$field_name);
          break;
        default:
          throw new \Exception("Unrecognized attachment type {$field_type}");
      }
      if(!$obj) return null;
      if($la_mode)
      {
        // Recover image if missing from Laravel Admin
        $la_fpath = config('laravel-stapler.images.la_path')."/{$obj->att_file_name}";
        if(!file_exists($la_fpath))
        {
          copy($obj->path('admin'), $la_fpath);
        }
        
        return $obj->att_file_name;
      }
      return $obj->att;
    }
    return parent::mutateAttribute($key, $value);
  }  
}