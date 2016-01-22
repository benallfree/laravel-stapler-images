<?php
use Imagine\Image\ImageInterface;

/* Specify an array of sizes to be maintained */
$sizes = [
  'large' => '640x640#',
  'featured' => '585x585#',
  'medium' => '400x400#',
  'thumb' => '180x180#',
  'admin' => '100x100#',
  'tiny' => '75x75#',
];
  
foreach($sizes as $k=>$v)
{
  $sizes[$k] = [
    'dimensions'=>$v,
    'convert_options' => [
      'jpeg_quality' => 90,
      'resampling-filter' => ImageInterface::FILTER_CATROM,
    ],
  ];
}

return [
  'table_name'=>'attachments',
  'la_path'=>storage_path('admin/uploads'),
  'sizes'=>$sizes,
];
