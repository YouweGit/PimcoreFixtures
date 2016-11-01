<?php


namespace Fixtures\Alice\Providers;

use Pimcore\File;

class General
{

    /**
     * @param $number
     * @return bool true if pair and false if impair
     */
    public function pairNumberToTrue($number)
    {
        return $number % 2 === 0;
    }

    /**
     * @param $number
     * @return bool true if pair and false if impair
     */
    public function impairNumberToTrue($number)
    {
        return $number % 2 !== 0;
    }

    /**
     * @param $min
     * @param $module
     * @param $value
     */
    public function modularize($min, $module, $value)
    {
        return $min + ($value % $module);
    }

    /**
     * @param string $value
     * @param null|string $language
     * @return string
     */
    public function validFilename($value, $language = null)
    {
        return File::getValidFilename($value, $language);
    }
}
