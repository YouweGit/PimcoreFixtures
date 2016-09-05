<?php

use Fixtures\Console\Command\LoadFixturesCommand;
use Fixtures\FixtureLoader;
use Fixtures\Repository\FolderRepository;
use Pimcore\Controller\Action\Admin;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Folder;

class PimcoreFixtures_AdminController extends Admin {

    public function settingsAction() {
        $this->enableLayout();
    }

    public function loadFixturesAction() {
        $this->disableViewAutoRender();
        $fixtureFiles = new FixtureLoader();
        foreach(FixtureLoader::getFixturesFiles() as $fixtureFile){
            $fixtureFiles->load($fixtureFile);
        }
        $this->_helper->json(['success'=> true]);
    }

    public function getFolderByPathAction() {
        $query = $this->getRequest()->getParam('query');
        $foldersRepo = new FolderRepository();
        $folders = $foldersRepo->getFoldersByQuery($query);

        $data = [];
        /** @var Folder $folder */
        foreach ($folders as $folder) {
            $data[] = [
                'id'       => $folder->getId(),
                'fullPath' => $folder->getFullpath()
            ];
        }

        return $this->_helper->json([
            'folders' => $data,
            'success'=> true
        ]);
    }

    public function generateFixturesAction() {
        $folderId = (int)$this->getRequest()->getParam('folderId');
        $filename = $this->getRequest()->getParam('filename');
        $levels = (int)$this->getRequest()->getParam('levels');
        $generator = new \Fixtures\Generator($folderId, $filename, $levels);
        $generator->generateFixturesForFolder();

        $this->_helper->json(['success'=> true]);
    }
}

