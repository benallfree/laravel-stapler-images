<?php

namespace BenAllfree\LaravelStaplerImages\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;
use BenAllfree\LaravelStaplerImages\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ImageReprocess extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'images:reprocess';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Reprocess all images.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {
    $images = Image::all();
    foreach($images as $i)
    {
      if($this->option('force')==null && !$i->should_reprocess()) continue;
      echo("Processing {$i->att->url()}\n");
      try
      {
        $i->att->reprocess();
        $i->save();
      } catch (FileNotFoundException $e)
      {
        echo("\tFile not found.");
      }
    }
  }
  

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
#      array('example', InputArgument::REQUIRED, 'An example argument.'),
    );
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
      array('force', null, InputOption::VALUE_OPTIONAL, 'Force reprocessing.', null),
    );
  }

}
