<?php
/**
 * Created by PhpStorm.
 * User: burycel
 * Date: 14-8-16
 * Time: 15:50
 */

namespace FixtureBundle\Repository;


use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;

class FolderRepository
{

    /**
     * @param $query
     * @return AbstractObject[]
     */
    public function getFoldersByQuery($query = null)
    {
        $folders = new DataObject\Listing();
        $folders->setObjectTypes([AbstractObject::OBJECT_TYPE_FOLDER]);

        if ($query) {
            $folders->setCondition('CONCAT(o_path, o_key) LIKE ?', '%' . $query . '%');
        }

        return $folders->getObjects();
    }
}
