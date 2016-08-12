<?php


namespace Fixtures\Alice\Providers;

class Images
{
    /**
     * @var string
     */
    private $assetsPath;

    /**
     * @param string $assetsPath
     */
    public function __construct($assetsPath) {
        $this->assetsPath = $assetsPath;
    }

    /**
     * @param $filename
     * @return string
     * @throws \Exception
     */
    public function localImage($filename)
    {
        $fileFullPath = $this->assetsPath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fileFullPath) === false) {
            throw new \Exception(sprintf('Image not found at "%s"', $fileFullPath));
        }
        return fopen($fileFullPath, 'r+');

    }
    /**
     * @return string
     * @throws \Exception
     */
    public function localRandomImage()
    {

        $imgs =  glob($this->assetsPath . DIRECTORY_SEPARATOR . "*");
        if (count($imgs) === 0) {
            throw new \Exception(sprintf('No assets found at "%s"', $this->assetsPath . DIRECTORY_SEPARATOR));
        }
        shuffle($imgs);
        $fileFullPath =  current($imgs);
        if (file_exists($fileFullPath) === false) {
            throw new \Exception(sprintf('Image not found at "%s"', $fileFullPath));
        }
        return fopen($fileFullPath, 'r+');

    }


}