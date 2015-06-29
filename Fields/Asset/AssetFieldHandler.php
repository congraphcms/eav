<?php namespace Vizioart\Attributes\Handlers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\App;
use Vizioart\Attributes\Repositories\EntityRepository;
use Vizioart\Attributes\Managers\AttributeManager;

class AssetFieldHandler extends AbstractAttributeHandler
{
    /**
     * entityRepository
     *
     * @var object
     */
    public $entityRepository;

    /**
     * Create new AbstractAttributeHandler
     *
     * @param Eloquent $attributeValueModel
     *
     * @return void
     */
    public function __construct(AttributeManager $attributeManager,
                                Eloquent $attributeValueModel,
                                Eloquent $attributeOptionModel,
                                Eloquent $setAttributeModel,
                                FileModel $fileModel = null)
    {

        // init parent constructor
        parent::__construct($attributeManager, $attributeValueModel, $attributeOptionModel, $setAttributeModel);

        if ($fileModel == null) {
            $fileModel = App::make('FileModel');
        }

        $this->fileModel = $fileModel;
    }

    /**
     * Clean all related values for given entity
     *
     * Takes entity id,
     * and deletes all related values
     *
     * @param integer $entityID
     * @param integer $attributeID
     *
     * @return boolean
     *
     * @todo Check if there is need for returning false or there will be an exception if something goes wrong
     */
    public function sweepAfterEntities($entityIDs, $attributeID)
    {
        if (!is_array($entityIDs)) {
            $entityIDs = array(intval($entityIDs));
        }

        if (empty($entityIDs)) {
            return true;
        }
        
        $success = $this    ->attributeValueModel
                            ->where(function ($query) use ($entityIDs, $attributeID) {
                                $query    ->whereIn('entity_id', $entityIDs)
                                        ->where('attribute_id', '=', $attributeID);
                            })
                            ->delete();
        return !!$success;
    }

    /**
     * Clean all related values for given file
     *
     * Takes array of file ids,
     * and deletes all related values
     *
     * @param integer $fileIDs
     * @param integer $attributeID
     *
     * @return boolean
     *
     * @todo Check if there is need for returning false or there will be an exception if something goes wrong
     */
    public function sweepAfterFiles($fileIDs)
    {
        if (!is_array($fileIDs)) {
            $fileIDs = array(intval($fileIDs));
        }

        if (empty($fileIDs)) {
            return true;
        }
        
        $success = $this    ->attributeValueModel
                            ->where(function ($query) use ($fileIDs) {
                                $query    ->whereIn('value', $fileIDs);
                            })
                            ->delete();
        return !!$success;
    }

    /**
     * Check for specific rules and validation on attribute insert
     *
     * Called after standard attribute validation with referenced attribute params
     * depending on boolean value returned by this function attribute insert will continue or stop the execution
     *
     * @param array $params
     *
     * @return boolean
     */
    public function checkAttributeForInsert(array &$params)
    {
        if (!isset($params['data']) || !is_array($params['data'])) {
            $this->addErrors(
                array(
                    'Data needs to be defined for relation.'
                )
            );
            return false;
        }

        $data = $params['data'];

        if (!isset($data['filetypes'])) {
            $this->addErrors(
                array(
                    'File types must be defined.'
                )
            );
            return false;
        }

        $this->sortFileTypes($data);

        $params['data'] = $data;

        return true;
    }

    

    /**
     * Check for specific rules and validation on attribute update
     *
     * Called after standard attribute validation with referenced attribute params
     * depending on boolean value returned by this function attribute update will continue or stop the execution
     *
     * @param array $params
     *
     * @return boolean
     */
    public function checkAttributeForUpdate(array &$params)
    {
        if (!isset($params['data']) || !is_array($params['data'])) {
            $this->addErrors(
                array(
                    'Data needs to be defined for relation.'
                )
            );
            return false;
        }

        $data = $params['data'];

        if (!isset($data['filetypes'])) {
            $this->addErrors(
                array(
                    'File types must be defined.'
                )
            );
            return false;
        }

        $this->sortFileTypes($data);

        $params['data'] = $data;

        return true;
    }

    protected function sortFileTypes(array &$data)
    {
        $filetypes = $data['filetypes'];

        foreach ($filetypes as &$type) {
            $type = trim($type);
        }

        $data['filetypes'] = $filetypes;
    }

    /**
     * Make changes to attribute before handing it to application
     *
     * @param array $attribute
     *
     * @return object
     */
    public function transformAttribute($attribute)
    {
        $attribute->data = json_decode($attribute->data);
        return $attribute;
    }

    /**
     * Take attribute value and transform it for frontend output
     *
     * @param $value
     * @param $attribute
     * @param $options
     *
     * @return mixed
     */
    public function fetchValue($value, $attribute, $options)
    {
        $value->value = intval($value->value);
        if (empty($value->value)) {
            return $value;
        }
        
        $file = $this->fileModel->get_by_ids($value->value, true);

        if (!$file) {
            $value->value = null;
            return $value;
        }

        $value->value = $file;
        return $value;
    }

    /**
     * Take attribute values and batch transform them for frontend output
     *
     * @param $values
     * @param $with
     *
     * @return mixed
     */
    public function fetchValues($values, $lang_id, $with)
    {
        if (!in_array('assets', $with)) {
            return $values;
        }

        $fileIDs = array();

        foreach ($values as $entity_id => $fields) {
            foreach ($fields as $code => $field) {
                if (is_array($field->value)) {
                    foreach ($field->value as $value) {
                        if (intval($value)) {
                            $fileIDs[] = intval($value);
                        }
                    }
                } else {
                    if (intval($field->value)) {
                        $fileIDs[] = intval($field->value);
                    }
                }
            }
        }

        if (empty($fileIDs)) {
            return $values;
        }
        
        /// CHANGE !!!!!!!!!!!!!!!!!!!!
        $files = $this->fileModel->get_by_ids($fileIDs);

        if (!$files) {
            return $values;
        }
        
        foreach ($values as $entity_id => &$fields) {
            foreach ($fields as $code => &$field) {
                if (is_array($field->value)) {
                    $fileValues = array();
                    foreach ($field->value as $value) {
                        foreach ($files as $file) {
                            if ($file['id'] == $value) {
                                $fileValues[] = $file;
                                break;
                            }
                        }
                    }
                    $field->value = $fileValues;
                } else {
                    foreach ($files as $file) {
                        if ($file['id'] == $field->value) {
                            $field->value = $file;
                            break;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Take attribute value and transform it for output
     *
     * @param $value
     * @param $attribute
     * @param $options
     *
     * @return mixed
     */
    public function getValue($value, $attribute, $options)
    {
        $value->value = intval($value->value);
        if (empty($value->value)) {
            return $value;
        }
        
        $file = $this->fileModel->get_by_ids($value->value, true);

        if (!$file) {
            $value->value = null;
            return $value;
        }

        $value->value = $file;
        return $value;
    }

    /**
     * Provide default value for attribute
     *
     * @param $value
     * @param $attribute
     * @param $options
     *
     * @return mixed
     */
    public function getDefaultValue($attribute, $options = array())
    {
        $attributeSettings = $this->attributeManager->getDataType($attribute->data_type);
        
        if ($attributeSettings['has_multiple_values']) {
            $value = array();
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Perform validation and preparation, and
     * update attribute value in database using $attributeValueModel
     *
     * Takes attribute value params and Eloquent instance of attribute definition
     *
     * @param array $valueParams
     * @param Eloqunt $attributeDefinition
     *
     * @return boolean
     */
    public function updateValue($valueParams, Eloquent $attributeDefinition)
    {
        $success = parent::updateValue($valueParams, $attributeDefinition);
        // var_dump($valueParams);
        $valueParams = $this->prepareValue($valueParams, $attributeDefinition);

        if (!$success) {
            return false;
        }

        if (empty($valueParams['value'])) {
            return $success;
        }

        if (is_array($valueParams['value'])) {
            foreach ($valueParams['value'] as $value) {
                $file = $this->fileModel->where('id', '=', $value)->first();
                if ($file->type == 'image') {
                    $this->fileModel->create_image_versions($file, $attributeDefinition->code);
                }
            }

            return $success;
        }

        $file = $this->fileModel->where('id', '=', $valueParams['value'])->first();
        if ($file->type == 'image') {
            $this->fileModel->create_image_versions($file, $attributeDefinition->code);
        }

        return $success;
    }



    /**
     * Prepare attribute value for database
     *
     * Cast value to be string
     *
     * @param array $valueParams
     *
     * @return boolean
     */
    public function prepareValue($valueParams, $attribute)
    {
        // var_dump($valueParams);
        $attributeSettings = $this->attributeManager->getDataType($attribute->data_type);
        
        if ($attributeSettings['has_multiple_values']) {
            foreach ($valueParams['value'] as &$value) {
                if (empty($value['id'])) {
                    $value = null;
                }
                // var_dump($value);
                $value = intval($value['id']);
            }

            return $valueParams;
        } else {
            if (empty($valueParams['value']['id'])) {
                $valueParams['value'] = null;
            }
            $valueParams['value'] = intval($valueParams['value']['id']);
        }
        
        return $valueParams;
    }



    /**
     * Validate attribute value
     *
     * This function should be overriden by specific attribute handler
     *
     * @param array $valueParams
     * @param Eloqunt $attributeDefinition
     *
     * @return boolean
     */
    public function validateAttribute($valueParams, Eloquent $attributeDefinition)
    {
        $success = parent::validateAttribute($valueParams, $attributeDefinition);

        // var_dump($valueParams);

        $valueParams = $this->prepareValue($valueParams, $attributeDefinition);

        if (empty($valueParams['value'])) {
            return $success;
        }



        if (is_array($valueParams['value'])) {
            foreach ($valueParams['value'] as $value) {
                $value = intval($value);
                $file = $this->fileModel->get_by_ids($value, true);
                if (!$file) {
                    $success = false;
                    $this->addErrors(
                        array(
                            'Invalid file ID. (' . $value . ')'
                        )
                    );
                }

                if (!in_array($file['extension'], $attributeDefinition->data->filetypes)) {
                    $success = false;
                    $this->addErrors(
                        array(
                            'Invalid file extension. (' . $file['extension'] . ')'
                        )
                    );
                }
            }

            return $success;
        }

        $value = intval($valueParams['value']);
        // $exists = $this->fileModel->exists($value);
        $file = $this->fileModel->get_by_ids($value, true);
        if (!$file) {
            $success = false;
            $this->addErrors(
                array(
                    'Invalid file ID. (' . $value . ')'
                )
            );
        }

        if (!in_array($file['extension'], $attributeDefinition->data->filetypes)) {
            $success = false;
            $this->addErrors(
                array(
                    'Invalid file extension. (' . $file['extension'] . ')'
                )
            );
        }

        return $success;
    }
}
