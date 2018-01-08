<?php


namespace FixtureBundle\Alice\Providers;


class Assets
{
    /**
     * @var string
     */
    private $assetsPath;

    /**
     * @param string $assetsPath
     */
    public function __construct($assetsPath)
    {
        $this->assetsPath = $assetsPath;
    }

    /**
     * @param $filename
     * @return string
     * @throws \Exception
     */
    public function localAsset($filename)
    {
        $fileFullPath = $this->assetsPath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fileFullPath) === false) {
            throw new \Exception(sprintf('Image not found at "%s"', $fileFullPath));
        }

        return fopen($fileFullPath, 'r+');

    }

    /**
     * @param string $extension
     * @return string
     * @throws \Exception
     */
    public function localRandomAsset($extension = '*')
    {
        $assets = glob($this->assetsPath . DIRECTORY_SEPARATOR . '*.' . $extension, GLOB_BRACE);
        if (count($assets) === 0) {
            throw new \Exception(sprintf('No assets found at "%s"', $this->assetsPath . DIRECTORY_SEPARATOR));
        }
        shuffle($assets);
        $fileFullPath = current($assets);
        if (file_exists($fileFullPath) === false) {
            throw new \Exception(sprintf('Asset not found at "%s"', $fileFullPath));
        }

        return fopen($fileFullPath, 'r+');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function localRandomImage()
    {
        return $this->localRandomAsset('{jpg,jpeg,png,gif}');
    }
}
