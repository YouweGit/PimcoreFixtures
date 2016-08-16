<?php


namespace Fixtures\Alice\Providers;

use Exception;
use Faker\Provider\Base;
use Pimcore\Model\Object\AbstractObject;

class ObjectReference
{
    /** @var array */
    private static $objects;

    /**
     * @param array $objects
     */
    function __construct(&$objects)
    {
        self::$objects = $objects;
    }

    /**
     * Return a random element by class
     *
     * @param $class
     * @return null|AbstractObject
     */
    public function randomElementByClass($class)
    {
        $objectsWithClass = $this->removeFirstLevel();
        $objectsWithClass = $this->filterByClass($class, $objectsWithClass);
        return Base::randomElement($objectsWithClass);
    }

    /**
     * Return a random element by class from specified file
     *
     * @param string $filename
     * @param string $class
     * @return null|AbstractObject
     * @throws Exception
     */
    public function randomElementByFilenameAndClass($filename, $class)
    {

        $this->ensureKeyExists($filename, self::$objects);
        $objectsWithClass = $this->filterByFilenameAndClass($filename, $class);
        return Base::randomElement($objectsWithClass);
    }

    /**
     * Return a random elements by class
     *
     * @param string $class
     * @param int $count
     * @return null|AbstractObject
     */
    public function randomElementsByClass($class, $count)
    {
        $objectsWithClass = $this->removeFirstLevel();
        $objectsWithClass = $this->filterByClass($class, $objectsWithClass);
        return Base::randomElement($objectsWithClass, $count);
    }

    /**
     * Return a random elements by class from specified file
     *
     * @param string $filename
     * @param string $class
     * @param int $count
     * @return null|AbstractObject
     * @throws Exception
     */
    public function randomElementsByFilenameAndClass($filename, $class, $count)
    {
        $this->ensureKeyExists($filename, self::$objects);
        $objectsWithClass = $this->filterByFilenameAndClass($filename, $class);
        return Base::randomElements($objectsWithClass, $count);
    }

    /**
     * Return a random element by class from specified file
     *
     * @param string $filename
     * @param string $class
     * @param int $current
     * @param int $module
     * @return null|AbstractObject
     * @throws Exception
     */
    public function moduleElementByFilenameAndClass($filename, $class, $current, $module = 3)
    {
        $this->ensureKeyExists($filename, self::$objects);
        $objectsWithClass = $this->filterByFilenameAndClass($filename, $class);

        $keys = array_keys($objectsWithClass);
        $ceil = (int)ceil($current / $module) - 1;

        $this->ensureKeyExists($ceil, $keys);
        $returnKey = $keys[$ceil];
        return $objectsWithClass[$returnKey];
    }

    /**
     * @param array $objectsWithClass
     *
     * @return array
     */
    private function removeFirstLevel($objectsWithClass = [])
    {
        foreach (self::$objects as $filename => $objectsInFile) {
            $objectsWithClass = array_merge($objectsWithClass, $objectsInFile);
        }
        return $objectsWithClass;
    }

    /**
     * @param string $filename
     * @param string $class
     * @return array
     */
    private function filterByFilenameAndClass($filename, $class)
    {
        return array_filter(self::$objects[$filename], function ($key, $object) use ($class) {
            return is_object($key) && get_class($key) === $class;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param string $class
     * @param string $objectsWithClass
     * @return array
     */
    private function filterByClass($class, $objectsWithClass)
    {
        return array_filter($objectsWithClass, function ($key, $object) use ($class) {
            return is_object($key) && get_class($key) === $class;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param string|int $filename
     * @param array $array
     * @throws Exception
     */
    private function ensureKeyExists($filename, $array)
    {
        if (array_key_exists($filename, $array) === false) {
            throw new Exception("Cannot find '$filename' in : " . implode(', ', array_keys(self::$objects)));
        }
    }
}