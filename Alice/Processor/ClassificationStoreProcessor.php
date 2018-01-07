<?php


namespace FixtureBundle\Alice\Processor;


use Nelmio\Alice\ProcessorInterface;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\ClassDefinition\Data\Classificationstore;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\Folder;

class ClassificationStoreProcessor implements ProcessorInterface
{


    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function preProcess($object)
    {
        if (($object instanceof AbstractObject || $object instanceof Concrete) && !$object instanceof Folder && $storeName = $this->objectHasClassificationStore($object)) {
            $getter = 'get' . ucfirst($storeName);
            $setter = 'set' . ucfirst($storeName);
            /** @var \Pimcore\Model\Object\Classificationstore $classificationStore */
            $classificationStore = $object->$getter();
            if (count($classificationStore->getItems()) > 1) {
                $classificationStore->setObject($object);
                $classificationStore->setFieldname($storeName);
                $object->$setter($classificationStore);
            }
        }

    }

    /**
     * @param AbstractObject|Concrete $object
     * @return null|string
     */
    private function objectHasClassificationStore($object)
    {
        foreach ($object->getClass()->getFieldDefinitions() as $field) {
            if ($field instanceof Classificationstore) {
                return $field->getName();
            }
        }

        return null;
    }

    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function postProcess($object)
    {
    }
}
