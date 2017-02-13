<?php
namespace Fixtures;

use Fixtures\Alice\Persister\PimcorePersister;
use Fixtures\Alice\Processor\ClassificationStoreProcessor;
use Fixtures\Alice\Processor\DocumentProperties;
use Fixtures\Alice\Processor\UserProcessor;
use Fixtures\Alice\Processor\WorkspaceProcessor;
use Fixtures\Alice\Providers\Assets;
use Fixtures\Alice\Providers\ClassificationStoreProvider;
use Fixtures\Alice\Providers\DateTime;
use Fixtures\Alice\Providers\General;
use Fixtures\Alice\Providers\ObjectReference;
use Nelmio\Alice\Fixtures;
use Pimcore\File;

class FixtureLoader
{

    const FIXTURE_FOLDER = PIMCORE_WEBSITE_VAR . '/plugins/PimcoreFixtures/fixtures';
    const IMAGES_FOLDER  = PIMCORE_WEBSITE_VAR . '/plugins/PimcoreFixtures/images';

    private static $objects = [];

    /**
     * @param array|null $specificFiles Array of files in fixtures folder
     * @return array
     */
    public static function getFixturesFiles($specificFiles = [])
    {
        self::createFolderDependencies([
            self::FIXTURE_FOLDER,
            self::IMAGES_FOLDER
        ]);

        if (is_array($specificFiles) && count($specificFiles) > 0) {
            $fixturesFiles = glob(self::FIXTURE_FOLDER . '/{' . implode(',', $specificFiles) . '}.yml', GLOB_BRACE);
        } else {
            $fixturesFiles = glob(self::FIXTURE_FOLDER . '/*.yml');
        }
        usort($fixturesFiles, function ($a, $b) {
            return strnatcasecmp($a, $b);
        });

        return $fixturesFiles;
    }

    /**
     * @param string $fixtureFile
     */
    public function load($fixtureFile)
    {
        $providers = [
            new Assets(self::IMAGES_FOLDER), // Will provide functionality to load images
            new ClassificationStoreProvider(),
            new General(),
            new DateTime(),
            new ObjectReference(self::$objects),
        ];
        $processors = [
            new ClassificationStoreProcessor(),
            new UserProcessor(),
            new WorkspaceProcessor(),
            new DocumentProperties()
        ];
        $persister = new PimcorePersister(); // set parameter to true to update existing objects
        $basename = basename($fixtureFile);
        self::$objects[ $basename ] = array_merge(self::$objects, Fixtures::load($fixtureFile, $persister, ['providers' => $providers], $processors));
    }

    /**
     * Makes sure all folders are created so glob does not throw any error
     * @param array $folders
     */
    private static function createFolderDependencies($folders)
    {
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                File::mkdir($folder);
            }
        }
    }
}
