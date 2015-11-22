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

		// 1.0 Create enity_statuses table
		// ---------------------------------------------------

		Schema::create('entity_statuses', function($table) {

			// primary key, autoincrement
			$table->increments('id');

			// relations
			
			// Entity type ID
			$table->integer('entity_id');

			// Attribute set ID
			$table->integer('workflow_point_id');

			// Locale ID
			$table->integer('locale_id');

			// status state (active, history, scheduled)
			$table->string('state', 50);

			// status schedule date
			$table->timestamp('scheduled_at')->nullable();
			
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

			// Entity type API endpoint
			$table->string('endpoint', 100)->unique();

			// Entity type name
			$table->string('name', 250)->default('');

			// Entity type plural name
			$table->string('plural_name', 250)->default('');

			// Flag for localized entity types
			$table->boolean('localized')->default(0);

			// Flag for workflow existance
			$table->boolean('has_workflow')->default(0);

			// Flag for localized workflow
			$table->boolean('localized_workflow')->default(0);

			// Foreign key for workflow
			$table->integer('workflow_id')->unsigned()->nullable();

			// Foreign key for workflow step that is default for this entity type
			$table->integer('default_step_id')->unsigned()->nullable();

			// // Type of parentig (none, paren-child, archive...)
			// $table->string('parent_type', 50)->default('default');
			
			// flag for ability of multiple sets
			$table->boolean('multiple_sets')->default(1);

			$table->integer('default_set_id')->default(0);
			
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
