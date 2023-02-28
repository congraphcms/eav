<?php
/*
 * This file is part of the congraph/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ 

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
/**
 * SampleBlogCommandSeeder
 * 
 * Populates DB with data for testing
 * 
 * @uses        Illuminate\Database\Schema\Blueprint
 * @uses        Illuminate\Database\Seeder
 * 
 * @author      Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright   Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package     congraph/eav
 * @since       0.1.0-alpha
 * @version     0.1.0-alpha
 */
class SampleBlogCommandSeeder extends Seeder {

    public function run()
    {
        $bus = App::make('Congraph\Core\Bus\CommandDispatcher');


        // WORKFLOWS
        $workflows = [
            [
                'name' => 'Default',
                'description' => 'Only one public state'
            ],
            [
                'name' => 'Basic Publishing',
                'description' => 'Testing workflows'
            ]
        ];

        $workflowResults = [];
        foreach ($workflows as $workflow)
        {
            $command = App::make(\Congraph\Workflows\Commands\Workflows\WorkflowCreateCommand::class);
            $command->setParams($workflow);
            // $command->setId($id);
            
            $result = $bus->dispatch($command);
            $workflowResults[] = $result;
        }

        $workflowPoints = [
            [
                'workflow_id' => $workflowResults[0]->id,
                'status' => 'public',
                'endpoint' => 'publish',
                'action' => 'Publish',
                'name' => 'Public',
                'description' => 'Public',
                'public' => 1,
                'deleted' => 0,
                'sort_order' => 0
            ],
            [
                'workflow_id' => $workflowResults[1]->id,
                'status' => 'trashed',
                'endpoint' => 'trash',
                'action' => 'Trash',
                'name' => 'Trashed',
                'description' => 'Trashed objects',
                'public' => 0,
                'deleted' => 1,
                'sort_order' => 0
            ],
            [
                'workflow_id' => $workflowResults[1]->id,
                'status' => 'draft',
                'endpoint' => 'move_to_drafts',
                'action' => 'Move to drafts',
                'name' => 'Draft',
                'description' => 'Draft objects',
                'public' => 0,
                'deleted' => 0,
                'sort_order' => 1
            ],
            [
                'workflow_id' => $workflowResults[1]->id,
                'status' => 'published',
                'endpoint' => 'publish',
                'action' => 'Publish',
                'name' => 'Published',
                'description' => 'Published objects',
                'public' => 1,
                'deleted' => 0,
                'sort_order' => 2
            ],
        ];

        $workflowPointResults = [];
        foreach ($workflowPoints as $workflowPoint)
        {
            $command = App::make(\Congraph\Workflows\Commands\WorkflowPoints\WorkflowPointCreateCommand::class);
            $command->setParams($workflowPoint);
            // $command->setId($id);
            
            $result = $bus->dispatch($command);
            $workflowPointResults[] = $result;
        }

        $workflowPointUpdates = [
            [
                'id' => $workflowPointResults[1]->id,
                'steps' => [
                    [
                        'id' => $workflowPointResults[2]->id,
                        'type' => 'workflow-point'
                    ]
                ]
            ],
            [
                'id' => $workflowPointResults[2]->id,
                'steps' => [
                    [
                        'id' => $workflowPointResults[1]->id,
                        'type' => 'workflow-point'
                    ],
                    [
                        'id' => $workflowPointResults[3]->id,
                        'type' => 'workflow-point'
                    ]
                ]
            ],
            [
                'id' => $workflowPointResults[3]->id,
                'steps' => [
                    [
                        'id' => $workflowPointResults[1]->id,
                        'type' => 'workflow-point'
                    ],
                    [
                        'id' => $workflowPointResults[2]->id,
                        'type' => 'workflow-point'
                    ]
                ]
            ],
        ];

        $workflowPointResults = [$workflowPointResults[0]];
        foreach ($workflowPointUpdates as $workflowPoint)
        {
            $command = App::make(\Congraph\Workflows\Commands\WorkflowPoints\WorkflowPointUpdateCommand::class);
            $command->setParams($workflowPoint);
            $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $workflowPointResults[] = $result;
        }


        // LOCALES
        $locales = [
            [
                'code' => 'en_US',
                'name' => 'English'
            ],
            [
                'code' => 'sr_RS',
                'name' => 'Srpski'
            ]
        ];

        $localeResults = [];
        foreach ($locales as $locale)
        {
            $command = App::make(\Congraph\Locales\Commands\Locales\LocaleCreateCommand::class);
            $command->setParams($locale);
            // $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $localeResults[] = $result;
        }


        // ENTITY TYPES
        $entityTypes = [
            [
                'code' => 'page',
                'endpoint' => 'pages',
                'name' => 'Page',
                'plural_name' => 'Pages',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => $workflowResults[1]->id,
                'default_point_id' => $workflowPointResults[2]->id,
                'localized_workflow' => 1
            ],
            [
                'code' => 'article',
                'endpoint' => 'articles',
                'name' => 'Article',
                'plural_name' => 'Articles',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => $workflowResults[1]->id,
                'default_point_id' => $workflowPointResults[2]->id,
                'localized_workflow' => 1
            ],
            [
                'code' => 'category',
                'endpoint' => 'categories',
                'name' => 'Category',
                'plural_name' => 'Categories',
                'multiple_sets' => 1,
                'localized' => 1,
                'workflow_id' => $workflowResults[0]->id,
                'default_point_id' => $workflowPointResults[0]->id,
                'localized_workflow' => 1
            ]
        ];
        $entityTypeResults = [];
        foreach ($entityTypes as $entityType)
        {
            $command = App::make(\Congraph\Eav\Commands\EntityTypes\EntityTypeCreateCommand::class);
            $command->setParams($entityType);
            // $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $entityTypeResults[] = $result;
        }

        // ATTRIBUTES
        $attributes = [
            [
                'code' => 'title',
                'field_type' => 'text',
                'admin_label' => 'Title',
                'admin_notice' => '',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => true,
                'filterable' => false,
                'searchable' => true
            ],
            [
                'code' => 'body',
                'field_type' => 'text',
                'admin_label' => 'Body',
                'admin_notice' => '',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'searchable' => true
            ],
            [
                'code' => 'meta_title',
                'field_type' => 'text',
                'admin_label' => 'Meta Title',
                'admin_notice' => '',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'searchable' => false
            ],
            [
                'code' => 'meta_description',
                'field_type' => 'text',
                'admin_label' => 'Meta Description',
                'admin_notice' => '',
                'localized' => true,
                'default_value' => '',
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'searchable' => false
            ],
            [
                'code' => 'featured_image',
                'field_type' => 'asset',
                'admin_label' => 'Featured Image',
                'admin_notice' => '',
                'localized' => false,
                'unique' => false,
                'required' => false,
                'filterable' => false,
                'searchable' => false
            ],
            [
                'code' => 'category',
                'field_type' => 'relation',
                'admin_label' => 'Category',
                'admin_notice' => '',
                'localized' => false,
                'unique' => false,
                'required' => false,
                'filterable' => true,
                'searchable' => false
            ],
            [
                'code' => 'page_template',
                'field_type' => 'select',
                'admin_label' => 'Template',
                'admin_notice' => '',
                'localized' => false,
                'unique' => false,
                'required' => true,
                'filterable' => true,
                'searchable' => false,
                'options' => [
                    [
                        'value' => 'default',
                        'label' => 'Default',
                        'default' => 1,
                        'locale' => 0
                    ],
                    [
                        'value' => 'home_page',
                        'label' => 'Home page',
                        'default' => 0,
                        'locale' => 0,
                    ],
                    [
                        'value' => 'contact_page',
                        'label' => 'Contact page',
                        'default' => 0,
                        'locale' => 0,
                    ],
                ]
            ],
            [
                'code' => 'name',
                'field_type' => 'text',
                'admin_label' => 'Name',
                'admin_notice' => '',
                'localized' => true,
                'unique' => false,
                'required' => true,
                'filterable' => true,
                'searchable' => true
            ]
        ];
        $attributeResults = [];
        foreach ($attributes as $attribute)
        {
            $command = App::make(\Congraph\Eav\Commands\Attributes\AttributeCreateCommand::class);
            $command->setParams($attribute);
            // $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $attributeResults[] = $result;
        }


        // ATTRIBUTE SETS
        $attributeSets = [
            [
                'code' => 'page-default',
                'name' => 'Page Default',
                'entity_type_id' => $entityTypeResults[0]->id,
                'primary_attribute_id' => $attributeResults[0]->id,
                'attributes' => [
                    [
                        'id' => $attributeResults[0]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[1]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[2]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[3]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[4]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[6]->id,
                        'type' => 'attribute'
                    ]
                ]
            ],
            [
                'code' => 'article-default',
                'name' => 'Article Default',
                'entity_type_id' => $entityTypeResults[1]->id,
                'primary_attribute_id' => $attributeResults[0]->id,
                'attributes' => [
                    [
                        'id' => $attributeResults[0]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[1]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[2]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[3]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[4]->id,
                        'type' => 'attribute'
                    ],
                    [
                        'id' => $attributeResults[5]->id,
                        'type' => 'attribute'
                    ]
                ]
            ],
            [
                'code' => 'category-default',
                'name' => 'Category Default',
                'entity_type_id' => $entityTypeResults[2]->id,
                'primary_attribute_id' => $attributeResults[7]->id,
                'attributes' => [
                    [
                        'id' => $attributeResults[7]->id,
                        'type' => 'attribute'
                    ]
                ]
            ]
        ];

        $attributeSetResults = [];
        foreach ($attributeSets as $attributeSet)
        {
            $command = App::make(\Congraph\Eav\Commands\AttributeSets\AttributeSetCreateCommand::class);
            $command->setParams($attributeSet);
            // $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $attributeSetResults[] = $result;
        }

        // ENTITIES
        $entities = [
            [
                'entity_type_id' => $entityTypeResults[0]->id,
                'attribute_set_id' => $attributeSetResults[0]->id,
                'status' => 'published',
                'fields' => [
                    'title' => [
                        'en_US' => 'Home page',
                        'sr_RS' => 'Pocetna strana'
                    ],
                    'meta_title' => [
                        'en_US' => 'Home page',
                        'sr_RS' => 'Pocetna strana'
                    ],
                    'meta_description' => [
                        'en_US' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex.',
                        'sr_RS' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius.'
                    ],
                    'body' => [
                        'en_US' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                        'sr_RS' => 'Lorem Ipsum је једноставно модел текста који се користи у штампарској и словослагачкој индустрији. Lorem ipsum је био стандард за модел текста још од 1500. године, када је непознати штампар узео кутију са словима и сложио их како би направио узорак књиге. Не само што је овај модел опстао пет векова, него је чак почео да се користи и у електронским медијима, непроменивши се. Популаризован је шездесетих година двадесетог века заједно са листовима летерсета који су садржали Lorem Ipsum пасусе, а данас са софтверским пакетом за прелом као што је Aldus PageMaker који је садржао Lorem Ipsum верзије.'
                    ],
                    'page_template' => 'home_page'
                ]
            ],
            [
                'entity_type_id' => $entityTypeResults[0]->id,
                'attribute_set_id' => $attributeSetResults[0]->id,
                'status' => 'published',
                'fields' => [
                    'title' => [
                        'en_US' => 'Contact page',
                        'sr_RS' => 'Kontakt strana'
                    ],
                    'meta_title' => [
                        'en_US' => 'Contact page',
                        'sr_RS' => 'Kontakt strana'
                    ],
                    'meta_description' => [
                        'en_US' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex.',
                        'sr_RS' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius.'
                    ],
                    'body' => [
                        'en_US' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.',
                        'sr_RS' => 'Насупрот веровању, Lorem Ipsum није насумично изабран и сложен текст. Његови корени потичу у делу класичне Латинске књижевности од 45. године пре нове ере, што га чини старим преко 2000 година. Richard McClintock, професор латинског на Hampden-Sydney колеџу у Вирџинији, је потражио дефиницију помало чудне речи "consectetur" из Lorem Ipsum пасуса и анализирајући делове речи у класичној књижевности отркио аутентичан извор. Lorem Ipsum долази из поглавља 1.10.32 и 1.10.33 књиге "de Finibus Bonorum et Malorum" (Екстреми Бога и Зла) коју је написао Cicerо 45. године пре нове ере. Књига говори о теорији етике, која је била врло популарна током Ренесансе. Прва реченица Lorem Ipsum модела "Lorem ipsum dolor sit amet..", долази из реченице у поглављу 1.10.32.'
                    ],
                    'page_template' => 'contact_page'
                ]
            ],
            [
                'entity_type_id' => $entityTypeResults[0]->id,
                'attribute_set_id' => $attributeSetResults[0]->id,
                'status' => 'published',
                'fields' => [
                    'title' => [
                        'en_US' => 'About page',
                        'sr_RS' => 'O nama'
                    ],
                    'meta_title' => [
                        'en_US' => 'About page',
                        'sr_RS' => 'O nama'
                    ],
                    'meta_description' => [
                        'en_US' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex.',
                        'sr_RS' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius.'
                    ],
                    'body' => [
                        'en_US' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).',
                        'sr_RS' => 'Позната је чињеница да ће читалац бити спутан правим читљивим текстом на страници када гледа њен распоред. Поента коришћења Lorem Ipsum модела је мање-више из разлога што је распоред слова и речи нормалан, у поређењу са "Овде иде текст, овде иде текст", и чини страницу као читљиви Енглески. Многи софтверски пакети за прелом као и веб едитори, користе Lorem Ipsum као основан модел текста, и интернет претрага за фразом "lorem ipsum" ће дати много сајтова у свом пред финалном стању. Разне верзије су еволуирале током година, неке пуком случајношћу, а неке намерно (убацивавши хумор у фразе).'
                    ],
                    'page_template' => 'default'
                ]
            ],
            [
                'entity_type_id' => $entityTypeResults[0]->id,
                'attribute_set_id' => $attributeSetResults[0]->id,
                'status' => 'published',
                'fields' => [
                    'title' => [
                        'en_US' => 'Sample page',
                        'sr_RS' => 'Primer stranice'
                    ],
                    'meta_title' => [
                        'en_US' => 'Sample page',
                        'sr_RS' => 'Primer stranice'
                    ],
                    'meta_description' => [
                        'en_US' => 'Quo in movet saperet efficiantur, aliquam voluptua efficiantur eum ex.',
                        'sr_RS' => 'Mea no nihil liberavisse, vero viris adipiscing sit ei, eu mei paulo moderatius.'
                    ],
                    'body' => [
                        'en_US' => 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.',
                        'sr_RS' => 'Постоји много верзија и варијација Lorem Ipsum пасуса, али већина је претрпела некакву промену, било да је то додавање неког хумора или насумична респодела речи која на крају не изгледа уверљиво. Ако планирате да користите пасусе Lorem Ipsum модела, требало би да будете сигурни да се у средини текста не крије нека сакривена или срамотна порука. Сви Lorem Ipsum генератори који се могу наћи на Интернету су направљени да понављају предходно дефинисане делове, што чини овај генератор првим правим на Интернету. Он користи речник од 200 латинских речи које су комбиноване са подоста шаблона реченица како би генерисао Lorem Ipsum који изгледа уверљиво. То значи да овде генерисани Lorem Ipsum не садржи понављање, нема убачен хумор или неке неочекиване речи и тако даље.'
                    ],
                    'page_template' => 'default'
                ]
            ],
        ];

        $entityResults = [];
        foreach ($entities as $entity)
        {
            $command = App::make(\Congraph\Eav\Commands\Entities\EntityCreateCommand::class);
            $command->setParams($entity);
            // $command->setId($workflowPoint['id']);
            
            $result = $bus->dispatch($command);
            $entityResults[] = $result;
        }
    }

}