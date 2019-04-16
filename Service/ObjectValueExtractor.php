<?php

namespace FixtureBundle\Service;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use ReflectionClass;

class ObjectValueExtractor
{
    /** @var DataObject\Concrete */
    private $object;

    private static $ignoredFields = [
        'o_classId',
        'o_className',
        'lazyLoadedFields',
        'o_class',
        'o_versions',
        'o___loadedLazyFields',
        'scheduledTasks',
        'omitMandatoryCheck',
        'o_id',
        'o_parentId',
        'o_parent',
        'o_type',
        'o_path',
        'o_index',
        'o_creationDate',
        'o_modificationDate',
        'o_userOwner',
        'o_userModification',
        'o_properties',
        'o_hasChilds',
        'o_siblings',
        'o_hasSiblings',
        'o_dependencies',
        'o_childs',
        'o_locked',
        'o_elementAdminStyle',
        '____pimcore_cache_item__',
        'o_key',
        'o_published'

    ];

    /**
     * @param AbstractObject $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function getDataForObject()
    {
        if ($this->object instanceof DataObject\Concrete) {
            $values = [];
            foreach ($this->object->getClass()->getFieldDefinitions() as $key => $def) {

                // Known fields that we don`t need their values
                if (!(
                    $def instanceof DataObject\ClassDefinition\Data\Nonownerobjects ||
                    $def instanceof DataObject\ClassDefinition\Data\CalculatedValue ||
                    $def instanceof DataObject\ClassDefinition\Data\Classificationstore ||
                    $def instanceof DataObject\ClassDefinition\Data\Fieldcollections ||
                    $def instanceof DataObject\ClassDefinition\Data\ObjectsMetadata ||
                    $def instanceof DataObject\ClassDefinition\Data\MultihrefMetadata ||
                    // Todo this is important, add support
                    $def instanceof DataObject\ClassDefinition\Data\Objectbricks
                )
                ) {
                    if ($def instanceof DataObject\ClassDefinition\Data\Localizedfields) {
                        foreach ($def->getFieldDefinitions() as $localizedKey => $localizedFd) {
                            $values[ $localizedKey ] = $this->getDataForField($this->object, $localizedKey, $localizedFd);

                        }
                    } else {
                        $values[ $key ] = $this->getDataForField($this->object, $key, $def);

                    }
                }
            }
        } else {
            $values = $this->filterVars($this->object);
        }
        $this->addSystemReferences($values);

        return $values;
    }

    /**
     * @param DataObject\Concrete $object
     * @param string $key
     * @param DataObject\ClassDefinition\Data $fieldDefinition
     * @return array
     */
    private function getDataForField($object, $key, $fieldDefinition)
    {
        if ($fieldDefinition instanceof DataObject\ClassDefinition\Data\Relations\AbstractRelations) {
            $relations = $object->getRelationData($key, !$fieldDefinition->isRemoteOwner(), null);

            $value = [];
            if (count($relations)) {
                if ($fieldDefinition instanceof DataObject\ClassDefinition\Data\ManyToOneRelation) {
                    $obj = AbstractObject::getById($relations[0]['id']);
                    $objKey = $this->getUniqueKey($obj);
                    $value = '@' . $objKey;
                } else {
                    foreach ($relations as $rel) {
                        $obj = AbstractObject::getById($rel['id']);
                        $objKey = $this->getUniqueKey($obj);
                        $value[] = '@' . $objKey;
                    }
                }

                return $value;
            }

            return null;
        }

        $getter = "get" . ucfirst($key);
        $fieldData = $object->$getter();
        $value = $fieldDefinition->getDataForEditmode($fieldData, $object);

        return $value;
    }

    /**
     * @return bool
     */
    public function hasObjectBrick()
    {
        if ($this->object instanceof DataObject\Concrete) {
            foreach ($this->object->getClass()->getFieldDefinitions() as $key => $def) {
                if ($def instanceof DataObject\ClassDefinition\Data\Objectbricks) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Object\Concrete $object
     * @param array $classArray Array containing the class data
     */
    public function addObjectBricksForObject($object, &$classArray)
    {
        if ($object instanceof DataObject\Concrete) {
            foreach ($object->getClass()->getFieldDefinitions() as $key => $def) {
                if ($def instanceof DataObject\ClassDefinition\Data\Objectbricks) {
                    $getter = 'get' . ucfirst($key);
                    /** @var \Pimcore\Model\DataObject\Objectbrick $objectBrick */
                    $objectBrick = $object->$getter();

                    $objectBrickGetter = current($objectBrick->getBrickGetters());
                    /** @var DataObject\Objectbrick\Data\AbstractData $objectBrickHolder */
                    $objectBrickHolder = $objectBrick->$objectBrickGetter();
                    if ($objectBrickHolder) {
                        /** @var DataObject\Objectbrick\Definition $objectBrickHolderDefinition */
                        $objectBrickHolderDefinition = $objectBrickHolder->getDefinition();
                        $classArray[ get_class($objectBrickHolder) ][ self::getUniqueKey($object) . '_' . $key . '_holder' ]['__construct'] = ['@' . self::getUniqueKey($object)];
                        foreach ($objectBrickHolderDefinition->getFieldDefinitions() as $objectBrickKey => $objectBrickDefinition) {
                            $getter = 'get' . ucfirst($objectBrickKey);
                            $classArray[ get_class($objectBrickHolder) ][ self::getUniqueKey($object) . '_' . $key . '_holder' ][ $objectBrickKey ] = $objectBrickHolder->$getter();
                        }

                        $classArray[ get_class($objectBrick) ][ self::getUniqueKey($object) . '_' . $key ] = [
                            '__construct' => ['@' . self::getUniqueKey($object), $key],
                            $key          => '@' . self::getUniqueKey($object) . '_' . $key . '_holder'
                        ];
                    }
                }
            }
        }
    }

    /**
     * Un-sets keys like o_classId, o_className .. see self::$ignoredFields
     * @param AbstractObject $child
     * @return array
     */
    private function filterVars($child)
    {

        $vars = $child->getObjectVars();


        foreach ($vars as $key => $var) {
            if (in_array($key, self::$ignoredFields, true)) {
                unset($vars[ $key ]);
            }
        }

        return $vars;
    }

    /**
     * @param array $vars
     */
    private function addSystemReferences(&$vars)
    {
        $parent = $this->object->getParent();
        // Special case when parent is object home
        if ($parent->getId() === 1) {
            $vars['parentId'] = 1;
        } else {
            $objKey = $this->getUniqueKey($parent);
            $parentKey = '@' . $objKey;
            $vars['parent'] = $parentKey;
        }

        $vars['key'] = $this->object->getKey();
        if ($this->object instanceof DataObject\Concrete) {
            $vars['published'] = $this->object->getPublished();
        }
    }

    /**
     * @param AbstractObject $child
     * @return string
     */
    public static function getUniqueKey($child)
    {
        $classReflect = new ReflectionClass($child);

        $className = lcfirst($classReflect->getShortName());
        // Convert camelCase to snake case
        $className = strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $className));

        $objKey = $className . '_' . $child->getKey() . '_' . $child->getId();
        // Replace any other characters to _
        $objKey = preg_replace('/[^0-9a-zA-Z]+/', '_', $objKey);

        return $objKey;
    }

}
