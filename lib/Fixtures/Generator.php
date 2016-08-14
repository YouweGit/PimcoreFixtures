<?php


namespace Fixtures;


use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\Yaml\Yaml;

class Generator
{


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
        'o_key' => 'key',
        'o_published' => 'published'
    ];


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
        $this->folder = Folder::getById($folderId);
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
        $fixtures = $this->getChildsRecursive($this->folder);
        foreach ($fixtures as $level => $fixtureData) {
            $this->writeToFile($fixtureData, $level);
        }

    }

    /**
     * @param AbstractObject $root
     * @param array $fixtures
     * @return array
     */
    private function getChildsRecursive($root, &$fixtures = [])
    {
        /** @var AbstractObject $child */
        foreach ($root->getChilds() as $child) {
            $currentLevel = $this->getCurrentLevel($child);
            $vars = $this->filterVars($child->getObjectVars(), $root);
            $fixtures[$currentLevel][get_class($child)][$child->getKey() . '_' . $currentLevel] = $vars;
            if ($child->hasChilds() && ($currentLevel < $this->maxLevels)) {
               $this->getChildsRecursive($child, $fixtures, ++$currentLevel);
            }
        }
        return $fixtures;
    }

    /**
     * @param AbstractObject $child
     * @return int
     */
    private function getCurrentLevel($child){
        $fullPath = substr($child->getFullPath(), strlen($this->folder->getFullPath()));
        return count(explode('/', $fullPath)) - 1;
    }
    /**
     * Outputs array to yml
     * @param array $data
     * @param int $level
     */
    private function writeToFile($data, $level = 1)
    {
        $yaml = Yaml::dump($data, 3);

        $filename = '000_' . $this->filename;
        if($this->maxLevels > 1 ){
            $filename .= '_' . $level;
        }

        if (!is_dir(FixtureLoader::FIXTURE_FOLDER)) {
            File::mkdir(FixtureLoader::FIXTURE_FOLDER);
        }

        $fullPath = FixtureLoader::FIXTURE_FOLDER . DIRECTORY_SEPARATOR . $filename . '.yml';
        file_put_contents($fullPath, $yaml);
    }

    /**
     * Unsets keys like o_classId, o_className .. see self::$ignoredFields
     * and replaces keys like o_key, o_published with values that can be converted to setters when importing see self::$convertFields
     * @param array $vars
     * @return array
     */
    private function filterVars($vars , $parent)
    {
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
        $parentKey = $parent->getKey();
        $vars['parent'] = "@$parentKey";

        return $vars;
    }
}