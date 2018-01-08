<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 07/01/2018
 * Time: 05:56
 */

namespace FixtureBundle\Service;

use Pimcore\File;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;


class Generator
{
    const ALL_OBJ_TYPES = [AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_FOLDER , AbstractObject::OBJECT_TYPE_VARIANT];


    /** @var Folder */
    private $folder;

    /** @var string */
    private $filename;
    /** @var int */
    private $maxLevels;

    /**
     * @param int|string $folderId
     * @param string $filename
     * @param int $maxLevels
     */
    public function __construct($folderId, $filename, $maxLevels)
    {
        $this->validateFields($folderId, $filename, $maxLevels);
        $this->folder = AbstractObject::getById($folderId);
        $this->filename = $filename;
        $this->maxLevels = (int)$maxLevels;
    }

    /**
     * @param $folderId
     * @param $filename
     * @param $maxLevels
     * @throws \Exception
     */
    private function validateFields($folderId, $filename, $maxLevels)
    {
        if (is_int($folderId) === false) {
            throw new \Exception('Folder id must be an integer');
        }
        if (preg_match('/^[a-z0-9_]*$/', $filename) === 0) {
            throw new \Exception('Filename must be snake_case');
        }
        if (is_int($maxLevels) === false) {
            throw new \Exception('Levels must be an integer');
        }
    }

    /**
     * Gets all children from specified folder and outputs to yml the result
     */
    public function generateFixturesForFolder()
    {
        $fixtures = $this->getChildrenRecursive($this->folder);
        foreach ($fixtures as $level => $fixtureClasses) {
            foreach ($fixtureClasses as $class => $fixtureData) {
                $this->writeToFile($fixtureData, $class, $level);
            }
        }
    }

    private function getChildrenRecursive($root, &$fixtures = [])
    {
        /** @var AbstractObject $child */
        foreach ($root->getChilds(self::ALL_OBJ_TYPES, true) as $child) {
            $currentLevel = $this->getCurrentLevel($child);

            $valueExtractor = new ObjectValueExtractor($child);

            $values = $valueExtractor->getDataForObject();
            $objKey = ObjectValueExtractor::getUniqueKey($child);
            $fixtures[ $currentLevel ][ (new ReflectionClass($child))->getShortName() ][ get_class($child) ][ $objKey ] = $values;

            if ($valueExtractor->hasObjectBrick()) {
                $valueExtractor->addObjectBricksForObject($child, $fixtures[ $currentLevel ][ (new ReflectionClass($child))->getShortName() ]);
            }

            if ($child->getChilds(self::ALL_OBJ_TYPES, true) && ($currentLevel < $this->maxLevels)) {
                $this->getChildrenRecursive($child, $fixtures);
            }
        }

        return $fixtures;
    }

    /**
     * Gets the level relative to home folder
     * @param AbstractObject $child
     * @return int
     */
    public static function getCurrentLevel($child)
    {
        $fullPath = substr($child->getFullPath(), 1);

        return count(explode('/', $fullPath)) - 1;
    }


    /**
     * Outputs array to yml
     * @param array $data
     * @param string $class
     * @param int $level
     */
    private function writeToFile($data, $class, $level)
    {

        $yaml = Yaml::dump($data, 3);

        $fixturesFolder = FixtureLoader::FIXTURE_FOLDER . '_generated' . DIRECTORY_SEPARATOR;
        if (!is_dir($fixturesFolder)) {
            File::mkdir($fixturesFolder);
        }
        $class = strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $class));

        $filename = '099_' . $class;
        if ($this->maxLevels > 1 && file_exists($fixturesFolder . $filename . '.yml')) {
            $filename .= '_' . $level;
        }

        $fullPath = $fixturesFolder . $filename . '.yml';
        file_put_contents($fullPath, $yaml);
    }

}