<?php


namespace Fixtures\Alice\Processor;


use Nelmio\Alice\ProcessorInterface;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\User;
use Pimcore\Tool;

class UserProcessor implements ProcessorInterface
{


    /**
     * Processes an object before it is persisted to DB
     *
     * @param AbstractObject|Concrete $object instance to process
     */
    public function preProcess($object)
    {
        if ($object instanceof User) {
            $encryptedPass = Tool\Authentication::getPasswordHash($object->getName(), $object->getPassword());
            $object->setPassword($encryptedPass);
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
