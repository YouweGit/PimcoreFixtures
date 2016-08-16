<?php
namespace Fixtures;

use Fixtures\Alice\Processor\UserProcessor;

use Fixtures\Alice\Persister\PimcorePersister;
use Fixtures\Alice\Processor\ClassificationStoreProcessor;
use Fixtures\Alice\Providers\ClassificationStoreProvider;
use Fixtures\Alice\Providers\ObjectReference;
use Fixtures\Alice\Providers\General;
use Fixtures\Alice\Providers\Images;
use Nelmio\Alice\Fixtures;
use Pimcore\File;

class FixtureLoader {

    const FIXTURE_FOLDER = PIMCORE_WEBSITE_VAR . '/plugins/PimcoreFixtures/fixtures';
    const IMAGES_FOLDER = PIMCORE_WEBSITE_VAR . '/plugins/PimcoreFixtures/images';

    private static $objects = [];

    /**
     * @return array
     */
    public static function getFixturesFiles() {
        self::createFolderDependencies(array(
            self::FIXTURE_FOLDER,
            self::IMAGES_FOLDER
        ));

        $fixturesFiles = glob(self::FIXTURE_FOLDER . '/*.yml');
        usort($fixturesFiles, function ($a, $b) {
            return strnatcasecmp($a, $b);
        });
        return $fixturesFiles;
    }

    /**
     * @param string $fixtureFile
     */
    public function load($fixtureFile) {
        $providers = array(
            new Images(self::IMAGES_FOLDER), // Will provide functionality to load images
            new ClassificationStoreProvider(),
            new General(),
            new ObjectReference(self::$objects),
        );
        $processors = array(
            new ClassificationStoreProcessor(),
            new UserProcessor()
        );
        $persister = new PimcorePersister(true);
        $basename = basename($fixtureFile);
        self::$objects[$basename] = array_merge(self::$objects, Fixtures::load($fixtureFile, $persister, ['providers' => $providers], $processors));
    }

    /**
     * Makes sure all folders are created so glob does not throw any error
     * @param array $folders
     */
    private static function createFolderDependencies($folders) {
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                File::mkdir($folder);
            }
        }
    }
}