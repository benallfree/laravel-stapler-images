<?php

namespace BenAllfree\LaravelStaplerImages\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;
use BenAllfree\LaravelStaplerImages\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use BenAllfree\LaravelStaplerImages\Jobs\ReprocessImageJob;

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
    Image::query()->chunk(50, function($images) {
      foreach($images as $i)
      {
        if($this->option('force')==null && !$i->should_reprocess()) continue;
        echo("Processing {$i->url()}\n");
        try
        {
          if(config('laravel-stapler.images.use_queue'))
          {
            ReprocessImageJob::dispatch($i);
          } else {
            $i->reprocess();
          }
        } catch (FileNotFoundException $e)
        {
          echo("\tFile not found.");
        }
      }
    });
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
