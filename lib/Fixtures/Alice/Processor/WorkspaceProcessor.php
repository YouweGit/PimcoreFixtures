<?php


namespace Fixtures\Alice\Processor;


use Nelmio\Alice\ProcessorInterface;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\User;
use Pimcore\Model\User\Workspace\AbstractWorkspace;
use Pimcore\Tool;

class WorkspaceProcessor implements ProcessorInterface
{

    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function preProcess($object)
    {
        if ($object instanceof AbstractWorkspace) {
            $cPathObj = AbstractObject::getById($object->getCid());
            $cPath = $cPathObj->getFullPath();
            $object->setCpath($cPath);
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
