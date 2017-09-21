<?php

namespace BenAllfree\LaravelStaplerImages\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use BenAllfree\LaravelStaplerImages\Image;

class ReprocessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;
    public $tries = 5;
    public $image;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Image $image)
    {
      $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->image->reprocess();
    }
}
