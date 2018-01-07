<?php


namespace FixtureBundle\Alice\Providers;

use Pimcore\Model\Object\Product;

class ClassificationStoreProvider
{

    /**
     * @param $data
     * @return mixed
     */
    public function classificationStore($data)
    {
        $decodedData = json_decode($data, true);
        $classificationFieldDef = Product::create()->getClass()->getFieldDefinition('accessories');

        return $classificationFieldDef->getDataFromEditmode($decodedData, $classificationFieldDef);
    }
}
