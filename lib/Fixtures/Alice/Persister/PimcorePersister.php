<?php

namespace Fixtures\Alice\Persister;

use Exception;
use Nelmio\Alice\PersisterInterface;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\User\AbstractUser;
use Pimcore\Model\User\Permission;
use Pimcore\Model\User\Workspace;

class PimcorePersister implements PersisterInterface {

    /**
     * @var bool
     */
    private $ignorePathAlreadyExits;

    /**
     * @param bool $ignorePathAlreadyExits
     */
    public function __construct($ignorePathAlreadyExits = false) {
        $this->ignorePathAlreadyExits = $ignorePathAlreadyExits;
    }

    /**
     * Loads a fixture file
     *
     * @param AbstractObject array [object] $objects instance to persist in the DB
     * @throws \Exception
     */
    public function persist(array $objects) {
        
        foreach ($objects as $object) {
            switch (true) {
                case $object instanceof AbstractElement:
                case $object instanceof AbstractObject:
                    $this->persistObject($object);
                    break;
                case $object instanceof AbstractUser:
                    $this->persistUser($object);
                    break;
                // Add here cases of exception that don't even have a save method but they actually do
                case $object instanceof Permission\Definition:
                    $this->persistClassWithSave($object);
                    break;
                case $object instanceof Workspace\Object:
                    var_dump('caught');
//                    $this->persistWorkspace($object);
                    break;
                default:
                    var_dump(get_class($object));
            }
        }
    }

    /**
     * @param AbstractObject $object
     */
    private function persistObject($object) {
        if ($this->ignorePathAlreadyExits === true) {
            if ($parent = $object->getParent()) {

                $path = str_replace('//', '/', $parent->getFullPath() . '/');
                $object->setPath($path);
            }
            $tmpObject = $object::getByPath($object->getFullPath());

            if ($tmpObject) {
                $objClass = get_class($object);
                if($tmpObject instanceof $objClass){
                    $object->setId($tmpObject->getId());
                } else {
                    $tmpObject->delete();
                }
            }
        }
        $object->save();
    }

    /**
     * @param AbstractUser $object
     */
    private function persistUser($object) {

        if ($this->ignorePathAlreadyExits === true) {
            $tmpObj = $object::getByName($object->getName());

            if($tmpObj){
                $object->setId($tmpObj->getId());
            }
        }
        $object->save();

    }

    /**
     * @param \stdClass $object
     */
    private function persistClassWithSave($object) {
        $object->save();
    }

    /**
     * Finds an object by class and id
     *
     * @param  string|AbstractObject $class
     * @param  int $id
     * @return mixed
     */
    public function find($class, $id) {

        $obj = $class::getById($id);
        if (!$obj) {
            throw new \UnexpectedValueException('Object with Id ' . $id . ' and Class ' . $class . ' not found');
        }

        return $obj;
    }
}
