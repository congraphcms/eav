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
 * SampleBlogSeeder
 * 
 * Populates DB with data for testing
 * 
 * @uses        Illuminate\Database\Schema\Blueprint
 * @uses        Illuminate\Database\Seeder
 * 
 * @author      Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright   Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package     cookbook/eav
 * @since       0.1.0-alpha
 * @version     0.1.0-alpha
 */
class SampleBlogSeeder extends Seeder {

    public function run()
    {
        DB::table('entity_types')->insert([
            [
                'code' => 'page',
                'endpoint' => 'pages',
                'name' => 'Page',
                'plural_name' => 'Pages',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => 2,
                'default_point_id' => 3,
                'localized_workflow' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'article',
                'endpoint' => 'articles',
                'name' => 'Article',
                'plural_name' => 'Articles',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => 2,
                'default_point_id' => 3,
                'localized_workflow' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'category',
                'endpoint' => 'categories',
                'name' => 'Category',
                'plural_name' => 'Categories',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => 1,
                'default_point_id' => 1,
                'localized_workflow' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('attributes')->insert([
            [
                'code' => 'title',
                'field_type' => 'text',
                'table' => 'attribute_values_varchar',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => true,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'body',
                'field_type' => 'text_area',
                'table' => 'attribute_values_text',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'meta_title',
                'field_type' => 'text',
                'table' => 'attribute_values_varchar',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'meta_description',
                'field_type' => 'text_area',
                'table' => 'attribute_values_text',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'featured_image',
                'field_type' => 'asset',
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
            [
                'code' => 'category',
                'field_type' => 'relation',
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
            [
                'code' => 'page_template',
                'field_type' => 'select',
                'table' => 'attribute_values_integer',
                'localized' => false,
                'default_value' => null,
                'unique' => false,
                'required' => true,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'name',
                'field_type' => 'text',
                'table' => 'attribute_values_varchar',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => true,
                'filterable' => false,
                'status' => 'user_defined',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
            
        ]);

        DB::table('attribute_options')->insert([
            [
                'value' => 'default',
                'label' => 'Default',
                'attribute_id' => 7,
                'default' => 1,
                'locale' => 0,
                'sort_order' => 0
            ],
            [
                'value' => 'home_page',
                'label' => 'Home page',
                'attribute_id' => 7,
                'default' => 0,
                'locale' => 0,
                'sort_order' => 1
            ],
            [
                'value' => 'contact_page',
                'label' => 'Contact page',
                'attribute_id' => 7,
                'default' => 0,
                'locale' => 0,
                'sort_order' => 2
            ],
        ]);

        DB::table('attribute_sets')->insert([
            [
                'code' => 'page-default',
                'name' => 'Page Default',
                'entity_type_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'article-default',
                'name' => 'Article Default',
                'entity_type_id' => 2,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'code' => 'category-default',
                'name' => 'Category Default',
                'entity_type_id' => 3,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('set_attributes')->insert([
            [
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'sort_order' => 0
            ],
            [
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'sort_order' => 1
            ],
            [
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'sort_order' => 2
            ],
            [
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'sort_order' => 3
            ],
            [
                'attribute_set_id' => 1,
                'attribute_id' => 5,
                'sort_order' => 4
            ],
            [
                'attribute_set_id' => 1,
                'attribute_id' => 7,
                'sort_order' => 5
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
                'attribute_set_id' => 2,
                'attribute_id' => 5,
                'sort_order' => 4
            ],
            [
                'attribute_set_id' => 2,
                'attribute_id' => 6,
                'sort_order' => 5
            ],
            [
                'attribute_set_id' => 3,
                'attribute_id' => 8,
                'sort_order' => 0
            ]
        ]);

        DB::table('entities')->insert([
            [
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ]);

        DB::table('attribute_values_varchar')->truncate();
        DB::table('attribute_values_varchar')->insert([
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Home page',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Home page',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Pocetna strana',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Pocetna strana',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Contact page',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Contact page',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Kontakt strana',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Kontakt strana',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'About page',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'About page',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 1,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'O nama',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 3,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'O nama',
            ],
        ]);
        
        DB::table('attribute_values_text')->truncate();
        DB::table('attribute_values_text')->insert([
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex. Idque ullum mandamus id mel, pri cu dolor civibus convenire. Nisl ludus eam ex, est ex electram ocurreret, an falli vituperatoribus vix. Ius id inani constituto, ex hinc altera nominavi mea, blandit legendos est te. Eu quo aperiam nonumes salutatus, eu vel vivendo appareat ocurreret. Vim et dictas imperdiet referrentur, te has mnesarchum liberavisse.',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex.',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius. Labore theophrastus ut mel, an viris vitae vim, amet dicam sententiae ei usu. Cu iudico albucius argumentum his, et qui quis dicunt verterem. Modo explicari duo no, ea qui integre fabellas. At enim soleat graecis sit, sea te harum concludaturque. Sea paulo dissentias disputando ne.',
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius.',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Ad eam reque homero molestiae, vim tantas tincidunt dissentias eu, vis malis iusto eu. Vim velit facete ut.',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Ad eam reque homero molestiae, vim tantas tincidunt dissentias eu, vis malis iusto eu. Vim velit facete ut.',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Harum nostrum abhorreant his in, deleniti delectus gubergren ad nam.',
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Harum nostrum abhorreant his in, deleniti delectus gubergren ad nam.',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Ex vix diceret quaerendum. Ad duo possit appetere partiendo, timeam regione labitur per ne, in sint melius accusamus sit. Viris iisque impedit no sed, eam alterum postulant mediocritatem ut. Ius ne omnis dicat clita, usu quidam dignissim maiestatis an. Te duo vide accusata, sea at tation cetero, soluta placerat in eos. Ut eam saperet luptatum, mel graecis repudiare honestatis no. Ex posse adversarium vim.

Ad nemore doctus labores eam, alii euismod euripidis pri ut. Explicari intellegam pri in, cu eos malis patrioque, nec ei nonumy efficiantur comprehensam. No veritus adversarium cum, an alia congue eligendi nec. Libris consetetur cum cu, his ex admodum scripserit comprehensam, ne per pertinax gloriatur.',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 1,
                'sort_order' => 0,
                'value' => 'Ex vix diceret quaerendum. Ad duo possit appetere partiendo, timeam regione labitur per ne, in sint melius accusamus sit.',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 2,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Ex vix diceret quaerendum. Ad duo possit appetere partiendo, timeam regione labitur per ne, in sint melius accusamus sit. Viris iisque impedit no sed, eam alterum postulant mediocritatem ut. Ius ne omnis dicat clita, usu quidam dignissim maiestatis an. Te duo vide accusata, sea at tation cetero, soluta placerat in eos. Ut eam saperet luptatum, mel graecis repudiare honestatis no. Ex posse adversarium vim.

Ad nemore doctus labores eam, alii euismod euripidis pri ut. Explicari intellegam pri in, cu eos malis patrioque, nec ei nonumy efficiantur comprehensam. No veritus adversarium cum, an alia congue eligendi nec. Libris consetetur cum cu, his ex admodum scripserit comprehensam, ne per pertinax gloriatur.',
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 4,
                'locale_id' => 2,
                'sort_order' => 0,
                'value' => 'Ex vix diceret quaerendum. Ad duo possit appetere partiendo, timeam regione labitur per ne, in sint melius accusamus sit.',
            ],
        ]);

        DB::table('attribute_values_integer')->insert([
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 5,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 1,
            ],
            [
                'entity_id' => 1,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 7,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 2,
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 5,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 2,
            ],
            [
                'entity_id' => 2,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 7,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 3,
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 5,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 3,
            ],
            [
                'entity_id' => 3,
                'entity_type_id' => 1,
                'attribute_set_id' => 1,
                'attribute_id' => 7,
                'locale_id' => 0,
                'sort_order' => 0,
                'value' => 1,
            ]
        ]);

        
    }

}