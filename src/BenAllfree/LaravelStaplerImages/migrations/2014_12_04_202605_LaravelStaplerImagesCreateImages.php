<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LaravelStaplerImagesCreateImages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('laravel-stapler-images.table_name'), function(Blueprint $table)
		{
			$table->increments('id');
      $table->string('original_file_name');
			$table->string('sizes_md5')->nullable();
      $table->string('image_file_name')->nullable();
      $table->integer('image_file_size')->nullable();
      $table->string('image_content_type')->nullable();
      $table->datetime('image_updated_at')->nullable();
      $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('laravel-stapler-images.table_name'));
	}

}