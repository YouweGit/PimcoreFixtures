<?php


namespace Fixtures;


use Pimcore\File;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;
use Symfony\Component\Yaml\Yaml;

class Generator {


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
        'o_published' => 'published'
    ];


    /** @var Folder */
    private $folder;

    /** @var string */
    private $saveToPath;

    /**
     * @param int|string $folderId
     * @param string $saveToPath
     */
    public function __construct($folderId, $saveToPath) {
        $this->folder = Folder::getById($folderId);
        $this->saveToPath = $saveToPath;
    }

    /**
     * Gets all childs from specified folder and outputs to yml the result
     */
    public function generateFixturesForFolder() {

        $fixtures = [];

        /** @var AbstractObject $child */
        foreach ($this->folder->getChilds([AbstractObject::OBJECT_TYPE_OBJECT]) as $child) {

            $vars = $child->getObjectVars();
            $this->filterVars($vars);

            $fixtures[get_class($child)][$child->getKey()] = $vars;
        }
        $this->writeToFile($fixtures);
    }

    /**
     * Outputs array to yml
     * @param array $data
     */
    private function writeToFile($data) {
        $yaml = Yaml::dump($data, 3);

        $fullPath = PIMCORE_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . $this->saveToPath;

        if (file_exists(dirname($fullPath))) {
            File::mkdir(dirname($fullPath));
        }

        file_put_contents($fullPath, $yaml);
    }

    /**
     * Unsets keys like o_classId, o_className .. see self::$ignoredFields
     * and replaces keys like o_key, o_published with values that can be converted to setters when importing see self::$convertFields
     * @param array $vars
     */
    private function filterVars(&$vars) {

        foreach ($vars as $key => $var) {
            if (array_key_exists($key, self::$ignoredFields)){
                unset($vars[$key]);
            }
        }


        foreach (self::$convertFields as $oldField => $newField) {
            if (array_key_exists($oldField, $vars)) {
                $vars[$newField] = $vars[$oldField];
                unset($vars[$oldField]);
            }
        }
    }
}