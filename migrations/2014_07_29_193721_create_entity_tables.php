<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * CreateEntityTables migration
 * 
 * Creates tables for entities in database needed for this package
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Migrations\Migration
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CreateEntityTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// 1.0 Create enities table
		// ---------------------------------------------------

		Schema::create('entities', function($table) {

			// primary key, autoincrement
			$table->increments('id');

			// relations
			
			// Entity type ID
			$table->integer('entity_type_id');
			// Attribute set ID
			$table->integer('attribute_set_id');
			
			// created_at and updated_at timestamps
			$table->timestamps();
		});


		// 1.1 Create enity_types table
		// ---------------------------------------------------

		Schema::create('entity_types', function($table) {

			// primary key, autoincrement
			$table->increments('id');
			
			// Entity type code for internal use
			$table->string('code', 100)->unique();

			// Entity type name
			$table->string('name', 250)->default('');

			// Entity type plural name
			$table->string('plural_name', 250)->default('');

			// // Type of parentig (none, paren-child, archive...)
			// $table->string('parent_type', 50)->default('default');
			
			// flag for ability of multiple sets
			$table->boolean('multiple_sets')->default(1);
			
			// created_at and updated_at timestamps
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

		// 1.0 Drop table enity_types

		Schema::drop('entity_types');

		// 1.1 Drop table enities

		Schema::drop('entities');
	}

}
