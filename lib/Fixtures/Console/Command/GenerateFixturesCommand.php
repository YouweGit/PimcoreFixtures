<?php
namespace Fixtures\Console\Command;

use Fixtures\FixtureLoader;
use Fixtures\Generator;
use Fixtures\Repository\FolderRepository;
use Nelmio\Alice\Fixtures;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Object\Folder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class GenerateFixturesCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('fixtures:generate')
            ->setDescription('Generate yml fixtures');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $helper = $this->getHelper('question');

        $folderRootQuestion = new ChoiceQuestion(
            '<info>Choose root folder?</info>',
            $this->formatFoldersToCommandChoices(),
            0
        );

        $rootFolder = $helper->ask($input, $output, $folderRootQuestion);


        $filenameQuestion = new Question('<info>Choose filename: </info>', 'test');
        $levelsQuestion = new Question('<info>Choose max levels deep (100): </info>', 100);

        $filename = $helper->ask($input, $output, $filenameQuestion);
        $levels = (int)$helper->ask($input, $output, $levelsQuestion);

        $output->writeln(
            ['<info>',
             'You chose: ',
             'Root folder: <comment>' . $rootFolder . '</comment>',
             'Filename: <comment>' . $filename . '</comment>',
             'Max level deep: <comment>' . $levels . '</comment>',
             '</info>'
            ]);

        $confirmationQuestion = new ConfirmationQuestion(
            '<info>Continue with this action? (y)</info>'
        );

        if (!$helper->ask($input, $output, $confirmationQuestion)) {
            return;
        }

        $rootId = Folder::getByPath('/')->getId();

        $generator = new Generator($rootId, $filename, $levels);
        $generator->generateFixturesForFolder();
        $output->writeln('<info>Done. Your fixtures are at: "' . FixtureLoader::FIXTURE_FOLDER . '".</info>');

    }

    private function formatFoldersToCommandChoices()
    {
        $foldersRepo = new FolderRepository();
        $folders = $foldersRepo->getFoldersByQuery();
        $foldersArr = [];
        foreach ($folders as $folder) {
            $foldersArr[] = $folder->getFullPath();
        }

        return $foldersArr;
    }

}
