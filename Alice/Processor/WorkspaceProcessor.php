<?php


namespace FixtureBundle\Alice\Processor;


use Nelmio\Alice\ProcessorInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\User;
use Pimcore\Tool;
use Pimcore\Model\User\Workspace;
use Pimcore\Model\Asset\Folder;

class WorkspaceProcessor implements ProcessorInterface
{

    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function preProcess($object)
    {
        if ($object instanceof Workspace\Object) {
            if ($object->getCid()) {
                $cPathObj = AbstractObject::getById($object->getCid());
                $cPath = $cPathObj->getFullPath();
                $object->setCpath($cPath);
            } elseif ($object->getCpath()) {
                $cIdObj = AbstractObject::getByPath($object->getCpath());
                $cId = $cIdObj->getId();
                $object->setCid($cId);
            }

        }
        if ($object instanceof Workspace\Asset) {
            if ($object->getCid()) {
                $cPathObj = Folder::getById($object->getCid());
                $cPath = $cPathObj->getFullPath();
                $object->setCpath($cPath);
            } elseif ($object->getCpath()) {
                $cIdObj = Folder::getByPath($object->getCpath());
                $cId = $cIdObj->getId();
                $object->setCid($cId);
            }
        }
    }

    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function postProcess($object)
    {
    }
}
