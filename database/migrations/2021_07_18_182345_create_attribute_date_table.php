<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * CreateAttributeDateTable migration
 * 
 * Creates table for date attribute values in database needed for this package
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Migrations\Migration
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CreateAttributeDateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// 1.0 Create attribute_values_date table
		// ---------------------------------------------------

		Schema::create('attribute_values_date', function($table) {

			// primary key, autoincrement
			$table->increments('id');

			// relations
			
			// Attribute ID
			$table->integer('attribute_id');
			// Entity ID
			$table->integer('entity_id');
			// Entity type ID
			$table->integer('entity_type_id');
			// Attribute Set ID
			$table->integer('attribute_set_id');
			// Language ID
			$table->integer('locale_id');

			// Sort order
			$table->integer('sort_order')->nullable()->default(0);

			// Attribute value
			$table->date('value')->nullable();

			// create indexes
			$table->index('entity_type_id');
			$table->index('attribute_set_id');
			$table->index('attribute_id');
			$table->index('entity_id');
			$table->index('locale_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		// 1.0 Drop table attribute_values_date

		Schema::drop('attribute_values_date');
	}

}
