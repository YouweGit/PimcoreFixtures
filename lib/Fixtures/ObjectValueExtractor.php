<?php
/**
 * Created by PhpStorm.
 * User: burycel
 * Date: 21-11-16
 * Time: 4:31
 */

namespace Fixtures;

use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object;
use ReflectionClass;

class ObjectValueExtractor {
    /** @var Object\Concrete */
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
    ];
    private static $convertFields = [
        'o_key'       => 'key',
        'o_published' => 'published',
    ];

    /**
     * @param AbstractObject $object
     */
    public function __construct($object) {
        $this->object = $object;
    }

    public function getDataForObject() {
        if ($this->object instanceof Object\Concrete) {
            $values = [];
            foreach ($this->object->getClass()->getFieldDefinitions() as $key => $def) {

                // Known fields that we don`t need their values
                if (!(
                    $def instanceof Object\ClassDefinition\Data\Nonownerobjects ||
                    $def instanceof Object\ClassDefinition\Data\CalculatedValue ||
                    $def instanceof Object\ClassDefinition\Data\Classificationstore ||
                    $def instanceof Object\ClassDefinition\Data\Fieldcollections ||
                    $def instanceof Object\ClassDefinition\Data\ObjectsMetadata ||
                    $def instanceof Object\ClassDefinition\Data\MultihrefMetadata ||
                    // Todo this is important, add support
                    $def instanceof Object\ClassDefinition\Data\Objectbricks
                )
                ) {
                    if ($def instanceof Object\ClassDefinition\Data\Localizedfields) {
                        foreach ($def->getFieldDefinitions() as $localizedKey => $localizedFd) {
                            $values[$localizedKey] = $this->getDataForField($this->object, $localizedKey, $localizedFd);

                        }
                    } else {
                        $values[$key] = $this->getDataForField($this->object, $key, $def);

                    }
                }
            }
        } else {
            $values = $this->filterVars($this->object);
        }
        $this->addParentReference($values);

        return $values;
    }

    /**
     * @param Object\Concrete             $object
     * @param string                      $key
     * @param Object\ClassDefinition\Data $fieldDefinition
     * @return array
     */
    private function getDataForField($object, $key, $fieldDefinition) {
        if ($fieldDefinition instanceof Object\ClassDefinition\Data\Relations\AbstractRelations) {
            $relations = $object->getRelationData($key, !$fieldDefinition->isRemoteOwner(), null);

            $value = [];
            if (count($relations)) {
                if ($fieldDefinition instanceof Object\ClassDefinition\Data\Href) {
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
     * Un-sets keys like o_classId, o_className .. see self::$ignoredFields
     * and replaces keys like o_key, o_published with values that can be converted to setters when importing see self::$convertFields
     * @param AbstractObject $child
     * @return array
     */
    private function filterVars($child) {

        $vars = $child->getObjectVars();


        foreach ($vars as $key => $var) {
            if (in_array($key, self::$ignoredFields, true)) {
                unset($vars[$key]);
            }
        }


        foreach (self::$convertFields as $oldField => $newField) {
            if (array_key_exists($oldField, $vars)) {
                $vars[$newField] = $vars[$oldField];
                unset($vars[$oldField]);
            }
        }

        return $vars;
    }

    /**
     * @param array $vars
     * @return string
     */
    private function addParentReference(&$vars) {
        $parent = $this->object->getParent();
        // Special case when parent is object home
        if ($parent->getId() === 1) {
            $vars['parentId'] = 1;
        } else {
            $objKey = $this->getUniqueKey($parent);
            $parentKey = '@' . $objKey;
            $vars['parent'] = $parentKey;
        }
    }

    /**
     * @param AbstractObject $child
     * @return string
     */
    public static function getUniqueKey($child) {
        $currentLevel = Generator::getCurrentLevel($child);
        $classReflect = new ReflectionClass($child);

        $className = lcfirst($classReflect->getShortName());
        // Convert camelCase to snake case
        $className = strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $className));

        $objKey = $className . '_' . $child->getKey() . '_' . $currentLevel;
        // Replace any other characters to _
        $objKey = preg_replace('/[^0-9a-zA-Z]+/', '_', $objKey);

        return $objKey;
    }

}