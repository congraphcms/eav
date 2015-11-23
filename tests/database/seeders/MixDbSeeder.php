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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
/**
 * MixDbSeeder
 * 
 * Populates DB with data for testing
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Seeder
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class MixDbSeeder extends Seeder {

	public function run()
	{
		DB::table('entity_types')->truncate();

		DB::table('entity_types')->insert([
			[
				'code' => 'none',
				'endpoint' => 'nones',
				'name' => 'None',
				'plural_name' => 'Nones',
				'multiple_sets' => 0,
				'localized' => 0,
				'workflow_id' => 2,
				'default_point_id' => 3,
				'localized_workflow' => 0,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'localized-only',
				'endpoint' => 'localized-only',
				'name' => 'Localized Only',
				'plural_name' => 'Localized Only\'s',
				'multiple_sets' => 0,
				'localized' => 1,
				'workflow_id' => 2,
				'default_point_id' => 3,
				'localized_workflow' => 0,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'fully_localized',
				'endpoint' => 'fully_localized',
				'name' => 'Fully Localized',
				'plural_name' => 'Fully Localized',
				'multiple_sets' => 0,
				'localized' => 1,
				'workflow_id' => 2,
				'default_point_id' => 3,
				'localized_workflow' => 1,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'different_workflow',
				'endpoint' => 'different_workflow',
				'name' => 'Different workflow',
				'plural_name' => 'Different workflow',
				'multiple_sets' => 0,
				'localized' => 0,
				'workflow_id' => 1,
				'default_point_id' => 1,
				'localized_workflow' => 0,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
		]);

		DB::table('attributes')->truncate();

		DB::table('attributes')->insert([
			[
				'code' => 'localized_text',
				'field_type' => 'text',
				'table' => 'attribute_values_varchar',
				'localized' => true,
				'default_value' => '123',
				'unique' => false,
				'required' => false,
				'filterable' => false,
				'status' => 'system_defined',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'simple_text',
				'field_type' => 'text',
				'table' => 'attribute_values_varchar',
				'localized' => false,
				'default_value' => null,
				'unique' => false,
				'required' => false,
				'filterable' => false,
				'status' => 'user_defined',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'localized_select',
				'field_type' => 'select',
				'table' => 'attribute_values_integer',
				'localized' => true,
				'default_value' => null,
				'unique' => false,
				'required' => false,
				'filterable' => false,
				'status' => 'system_defined',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'simple_select',
				'field_type' => 'select',
				'table' => 'attribute_values_integer',
				'localized' => false,
				'default_value' => null,
				'unique' => false,
				'required' => false,
				'filterable' => false,
				'status' => 'user_defined',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
		]);

		DB::table('attribute_options')->truncate();

		DB::table('attribute_options')->insert([
			[
				'value' => 'option1_locale1',
				'label' => 'Option 1 locale 1',
				'attribute_id' => 3,
				'default' => 1,
				'locale' => 1,
				'sort_order' => 0
			],
			[
				'value' => 'option1_locale2',
				'label' => 'Option 1 locale 2',
				'attribute_id' => 3,
				'default' => 1,
				'locale' => 2,
				'sort_order' => 0
			],
			[
				'value' => 'option2_locale1',
				'label' => 'Option 2 locale 1',
				'attribute_id' => 3,
				'default' => 0,
				'locale' => 1,
				'sort_order' => 1
			],
			[
				'value' => 'option2_locale2',
				'label' => 'Option 2 locale 2',
				'attribute_id' => 3,
				'default' => 0,
				'locale' => 2,
				'sort_order' => 1
			],
			[
				'value' => 'option1',
				'label' => 'Option 1',
				'attribute_id' => 4,
				'default' => 0,
				'locale' => 0,
				'sort_order' => 0
			],
			[
				'value' => 'option2',
				'label' => 'Option 2',
				'attribute_id' => 4,
				'default' => 1,
				'locale' => 0,
				'sort_order' => 1
			],
			
		]);

		DB::table('attribute_sets')->truncate();
		DB::table('set_attributes')->truncate();

		DB::table('attribute_sets')->insert([
			[
				'code' => 'set1',
				'name' => 'Set 1',
				'entity_type_id' => 1,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'set2',
				'name' => 'Set 2',
				'entity_type_id' => 2,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'set3',
				'name' => 'Set 3',
				'entity_type_id' => 3,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'code' => 'set4',
				'name' => 'Set 4',
				'entity_type_id' => 4,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

		DB::table('set_attributes')->insert([
			[
				'attribute_set_id' => 1,
				'attribute_id' => 2,
				'sort_order' => 0
			],
			[
				'attribute_set_id' => 1,
				'attribute_id' => 4,
				'sort_order' => 1
			],
			[
				'attribute_set_id' => 2,
				'attribute_id' => 1,
				'sort_order' => 0
			],
			[
				'attribute_set_id' => 2,
				'attribute_id' => 2,
				'sort_order' => 1
			],
			[
				'attribute_set_id' => 2,
				'attribute_id' => 3,
				'sort_order' => 2
			],
			[
				'attribute_set_id' => 2,
				'attribute_id' => 4,
				'sort_order' => 3
			],
			[
				'attribute_set_id' => 3,
				'attribute_id' => 1,
				'sort_order' => 0
			],
			[
				'attribute_set_id' => 3,
				'attribute_id' => 2,
				'sort_order' => 1
			],
			[
				'attribute_set_id' => 3,
				'attribute_id' => 3,
				'sort_order' => 2
			],
			[
				'attribute_set_id' => 3,
				'attribute_id' => 4,
				'sort_order' => 3
			],
			[
				'attribute_set_id' => 4,
				'attribute_id' => 2,
				'sort_order' => 0
			],
			[
				'attribute_set_id' => 4,
				'attribute_id' => 4,
				'sort_order' => 1
			]
		]);

		DB::table('entities')->truncate();
		DB::table('entities')->insert([
			[
				'entity_type_id' => 1,
				'attribute_set_id' => 1,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_type_id' => 4,
				'attribute_set_id' => 4,
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

		DB::table('entity_statuses')->truncate();
		DB::table('entity_statuses')->insert([
			[
				'entity_id' => 1,
				'workflow_point_id' => 4,
				'locale_id' => 0,
				'state' => 'active',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_id' => 2,
				'workflow_point_id' => 4,
				'locale_id' => 0,
				'state' => 'active',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_id' => 3,
				'workflow_point_id' => 4,
				'locale_id' => 1,
				'state' => 'active',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_id' => 3,
				'workflow_point_id' => 3,
				'locale_id' => 2,
				'state' => 'active',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'entity_id' => 4,
				'workflow_point_id' => 1,
				'locale_id' => 0,
				'state' => 'active',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
		]);

		DB::table('attribute_values_varchar')->truncate();
		DB::table('attribute_values_varchar')->insert([
			[
				'entity_id' => 1,
				'entity_type_id' => 1,
				'attribute_set_id' => 1,
				'attribute_id' => 2,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 'simple text',
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 1,
				'locale_id' => 1,
				'sort_order' => 0,
				'value' => 'text en',
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 1,
				'locale_id' => 2,
				'sort_order' => 0,
				'value' => 'text fr',
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 2,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 'simple text',
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 1,
				'locale_id' => 1,
				'sort_order' => 0,
				'value' => 'text en',
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 1,
				'locale_id' => 2,
				'sort_order' => 0,
				'value' => 'text fr',
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 2,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 'simple text',
			],
			[
				'entity_id' => 4,
				'entity_type_id' => 4,
				'attribute_set_id' => 4,
				'attribute_id' => 2,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 'simple text',
			],
		]);

		DB::table('attribute_values_integer')->truncate();
		DB::table('attribute_values_integer')->insert([
			[
				'entity_id' => 1,
				'entity_type_id' => 1,
				'attribute_set_id' => 1,
				'attribute_id' => 4,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 6,
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 3,
				'locale_id' => 1,
				'sort_order' => 0,
				'value' => 3,
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 3,
				'locale_id' => 2,
				'sort_order' => 0,
				'value' => 4,
			],
			[
				'entity_id' => 2,
				'entity_type_id' => 2,
				'attribute_set_id' => 2,
				'attribute_id' => 4,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 6,
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 3,
				'locale_id' => 1,
				'sort_order' => 0,
				'value' => 3,
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 3,
				'locale_id' => 2,
				'sort_order' => 0,
				'value' => 4,
			],
			[
				'entity_id' => 3,
				'entity_type_id' => 3,
				'attribute_set_id' => 3,
				'attribute_id' => 4,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 6,
			],
			[
				'entity_id' => 4,
				'entity_type_id' => 4,
				'attribute_set_id' => 4,
				'attribute_id' => 4,
				'locale_id' => 0,
				'sort_order' => 0,
				'value' => 6,
			],
		]);

		
	}

}