<?php

use Fixtures\Console\Command\LoadFixturesCommand;
use Fixtures\FixtureLoader;
use Pimcore\Controller\Action\Admin;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;

class PimcoreFixtures_AdminController extends Admin {
    public function settingsAction() {
        $this->enableLayout();
    }

    public function loadFixturesAction() {
        $this->disableViewAutoRender();
        foreach(FixtureLoader::getFixturesFiles() as $fixtureFile){
            FixtureLoader::load($fixtureFile);
        }
    }

    public function getFolderPathAction() {

        $folders = new Pimcore\Model\Object\Listing();
        $folders->setObjectTypes([AbstractObject::OBJECT_TYPE_FOLDER]);

        $query = $this->getRequest()->getParam('query');
        if ($query) {
            $folders->setCondition('CONCAT(o_path, o_key) LIKE ?', '%' . $query . '%');
        }

        $data = [];
        /** @var Folder $folder */
        foreach ($folders->getObjects() as $folder) {
            $data[] = [
                'id'       => $folder->getId(),
                'fullPath' => $folder->getFullpath()
            ];
        }

        return $this->_helper->json([
            'data' => $data
        ]);
    }

    public function generateFixturesAction() {
        $folderId = $this->getRequest()->getParam('id');
        $saveToPath = $this->getRequest()->getParam('saveToPath');


        $generator = new \Fixtures\Generator($folderId, $saveToPath);
        $generator->generateFixturesForFolder();

        return $this->_helper->json([
            'success' => true,
        ]);
    }

}

