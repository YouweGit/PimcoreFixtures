<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 07/01/2018
 * Time: 03:48
 */

namespace FixtureBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class FixtureBundle extends AbstractPimcoreBundle
{
	use PackageVersionTrait;

    protected function getComposerPackageName()
    {
        return 'youwe/pimcore-fixtures';
    } 
}
