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
		Schema::create(config('laravel-stapler.images.table_name'), function(Blueprint $table)
		{
			$table->increments('id');
      $table->string('original_file_name');
			$table->string('sizes_md5')->nullable();
      $table->string('att_file_name')->nullable();
      $table->integer('att_file_size')->nullable();
      $table->string('att_content_type')->nullable();
      $table->datetime('att_updated_at')->nullable();
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
		Schema::drop(config('laravel-stapler.images.table_name'));
	}

}
