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
		$this->package('benallfree/laravel-stapler-images');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
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
