<?php

namespace BenAllfree\LaravelStaplerImages\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;
use BenAllfree\LaravelStaplerImages\Image;

class ImageAdd extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'images:add';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Add an image.';

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
    $file = $this->argument('url');
    $i = Image::from_url($file,true);
    echo("Image ID is {$i->id}\n");
  }
  

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
      array('url', InputArgument::REQUIRED, 'An image URL or local file.'),
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
//      array('force', null, InputOption::VALUE_OPTIONAL, 'Force reprocessing.', null),
    );
  }

}
