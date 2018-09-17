<?php


namespace FixtureBundle\Alice\Providers;

use Pimcore\Model\DataObject\Product;

class ClassificationStoreProvider
{

    /**
     * @param $data
     * @return mixed
     */
    public function classificationStore($data)
    {
        $decodedData = json_decode($data, true);
        
        //@TODO Make this generic
        $classificationFieldDef = Product::create()->getClass()->getFieldDefinition('accessories');

        return $classificationFieldDef->getDataFromEditmode($decodedData, $classificationFieldDef);
    }
}
