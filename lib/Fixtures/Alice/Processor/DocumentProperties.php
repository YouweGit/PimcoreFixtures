<?php
/**
 * Created by PhpStorm.
 * User: burycel
 * Date: 12-2-17
 * Time: 7:09
 */

namespace Fixtures\Alice\Processor;


use Nelmio\Alice\ProcessorInterface;
use Pimcore\Model\Document;
use Pimcore\Model\Property;

class DocumentProperties implements ProcessorInterface
{
    /**
     * This might future version proof, because this properties might change
     * but this are set in Ext and there is no way to get them in php
     * @see pimcore/static/js/pimcore/document/properties.js
     * @var array
     */
    private $defaultProperties = [
        'language'              => [
            'data'        => 'nl',
            'type'        => 'text',
            'inheritable' => true,
        ],
        'navigation_exclude'    => [
            'data'        => false,
            'type'        => 'bool',
            'inheritable' => false,
        ],
        'navigation_name'       => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_title'      => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_relation'   => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_parameters' => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_anchor'     => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_target'     => [
            'data'        => null,
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_class'      => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_tabindex'   => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
        'navigation_accesskey'  => [
            'data'        => '',
            'type'        => 'text',
            'inheritable' => false,
        ],
    ];


    /**
     * Processes an object before it is persisted to DB
     * Converts an array of properties from format
     * [
     *      'language' => [
     *          'type' : string,
     *          'data' : string,
     *          'inherited' : bool,
     *          'inheritable' : bool,
     *      ],
     *      ...
     * ]
     *
     * into an array of Pimcore\Model\Property
     * [
     *   [
     *    object(Pimcore\Model\Property)
     *       'name' => string
     *       'data' => string
     *       'type' => string
     *       'inheritable' => bool
     *       'inherited' => bool
     *    ] , ...
     * ]
     * and replaces the original content with the newly generated format
     *
     * @param object $document instance to process
     */
    public function preProcess($document)
    {
        // TODO MAYBE EXTEND THIS FOR OTHER DOCUMENT TYPES
        if ($document instanceof Document && ($document->getType() === null || $document->getType() === 'page')) {
            // Get properties as array from fixtures and erase them
            $propertiesFromYaml = $document->getProperties();
            $document->setProperties(null);
            if (is_array($propertiesFromYaml) && !empty($propertiesFromYaml)) {
                $propertiesFromYaml = array_merge($this->defaultProperties, $propertiesFromYaml);
                $newProperties = [];
                foreach ($propertiesFromYaml as $key => $property) {
                    $newProperties[] = $this->getFormattedProperty($key, $property);
                }
                $document->setProperties($newProperties);
            }

        }
    }

    /**
     * Processes an object after it is persisted to DB
     *
     * @param object $object instance to process
     */
    public function postProcess($object)
    {
    }

    /**
     * @param string $key
     * @param array  $propertyData
     * @return Property
     * @see Document::setProperty() for default values
     * @throws \Exception
     */
    private function getFormattedProperty($key, $propertyData)
    {
        $this->validateProperty($key, $propertyData);

        $property = new Property();
        $property->setType($propertyData['type']);

        $property->setName($key);
        $property->setData($propertyData['data']);
        if (array_key_exists('inherited', $propertyData) === false) {
            $propertyData['inherited'] = false;
        }
        $property->setInherited($propertyData['inherited']);
        // Some properties do not support inheritable so we set it only when available
        if (array_key_exists('inheritable', $propertyData)) {
            $property->setInheritable($propertyData['inherited']);
        }
        // Pimcore will update this 2 properties after persisting it to the database
        // $property->setCid($documentId);
        // $property->setCtype("document");

        return $property;
    }

    /**
     * @param string $key
     * @param array  $propertyData
     * @throws \Exception
     */
    private function validateProperty($key, $propertyData)
    {
        $key = (string)$key;
        if (strlen($key) < 1) {
            throw new \Exception('Property key cannot be null on document, please check your yml files and make sure they match documentation provided at ' . __FILE__);
        }
        if (array_key_exists('data', $propertyData) === false) {
            throw new \Exception('Property data is mandatory on documents, please check your yml files and make sure they match documentation provided at ' . __FILE__);
        }
        if (array_key_exists('type', $propertyData) === false) {
            throw new \Exception('Property type is mandatory on documents, please check your yml files and make sure they match documentation provided at ' . __FILE__);
        }
    }
}