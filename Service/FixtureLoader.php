<?php
namespace FixtureBundle\Service;

use FixtureBundle\Alice\Providers\Assets;
use FixtureBundle\Alice\Persister\PimcorePersister;
use FixtureBundle\Alice\Processor\ClassificationStoreProcessor;
use FixtureBundle\Alice\Processor\DocumentProperties;
use FixtureBundle\Alice\Processor\UserProcessor;
use FixtureBundle\Alice\Processor\WorkspaceProcessor;
use FixtureBundle\Alice\Providers\ClassificationStoreProvider;
use FixtureBundle\Alice\Providers\DateTime;
use FixtureBundle\Alice\Providers\General;
use FixtureBundle\Alice\Providers\ObjectReference;
use Nelmio\Alice\Fixtures;
use Pimcore\File;

class FixtureLoader
{

    const FIXTURE_FOLDER = PIMCORE_PRIVATE_VAR. '/bundles/FixtureBundle/fixtures';
    const IMAGES_FOLDER  = PIMCORE_PRIVATE_VAR . '/bundles/FixtureBundle/images';

    private static $objects = [];
    /**
     * @var bool
     */
    private $omitValidation;
    /**
     * @var bool
     */
    private $checkPathExists;

    /**
     * FixtureLoader constructor.
     * @param bool $checkPathExists
     * @param bool $omitValidation
     */
    public function __construct($checkPathExists, $omitValidation) {
        $this->omitValidation = $omitValidation;
        $this->checkPathExists = $checkPathExists;
    }
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
            $fixturesFiles = glob(self::FIXTURE_FOLDER . '/{' . implode(',', $specificFiles) . '}.{yml,php}', GLOB_BRACE);
        } else {
            $fixturesFiles = glob(self::FIXTURE_FOLDER . '/*.{yml,php}',GLOB_BRACE);
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
        $persister = new PimcorePersister($this->checkPathExists, $this->omitValidation);
        $basename = basename($fixtureFile);
        self::$objects[ $basename ] = array_merge(self::$objects, Fixtures::load($fixtureFile, $persister, ['providers' => $providers], $processors));
    }

    /**
     * Makes sure all folders are created so glob does not throw any error
     * @param array $folders
     */
    private static function createFolderDependencies($folders)
    {
        var_dump($folders);
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                File::mkdir($folder);
            }
        }
    }
}
