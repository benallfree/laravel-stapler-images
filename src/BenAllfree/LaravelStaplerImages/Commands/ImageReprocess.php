<?php

namespace BenAllfree\LaravelStaplerImages\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

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
      if(!$i->should_reprocess()) continue;
      $i->save();
      echo("Processing {$i->image->url()}\n");
      $i->image->reprocess();
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
#      array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
    );
  }

}
