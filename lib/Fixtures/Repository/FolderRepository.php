<?php
/**
 * Created by PhpStorm.
 * User: burycel
 * Date: 14-8-16
 * Time: 15:50
 */

namespace Fixtures\Repository;


use Pimcore\Model\Object;
use Pimcore\Model\Object\AbstractObject;

class FolderRepository
{

    /**
     * @param $query
     * @return AbstractObject[]
     */
    public function getFoldersByQuery($query = null)
    {
        $folders = new Object\Listing();
        $folders->setObjectTypes([AbstractObject::OBJECT_TYPE_FOLDER]);

        if ($query) {
            $folders->setCondition('CONCAT(o_path, o_key) LIKE ?', '%' . $query . '%');
        }

        return $folders->getObjects();
    }
}
