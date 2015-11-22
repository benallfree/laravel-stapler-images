<?php namespace BenAllfree\LaravelStaplerImages;

use Illuminate\Support\ServiceProvider;

class LaravelStaplerImagesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
    $this->publishes([
      __DIR__.'/../../publish/config/images.php' => config_path('laravel-stapler/images.php'),
      __DIR__.'/../../publish/migrations' => base_path('database/migrations'),
    ]);
    
		//$this->package('benallfree/laravel-stapler-images');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
    $this->mergeConfigFrom(
        __DIR__.'/config/images.php', 'laravel-stapler.images'
    );
    
		$this->app->bind('image.reprocess', function($app)
		{
			return new Commands\ImageReprocess;
		});
    $this->commands('image.reprocess');
		$this->app->bind('image.add', function($app)
		{
			return new Commands\ImageAdd;
		});
    $this->commands('image.add');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
