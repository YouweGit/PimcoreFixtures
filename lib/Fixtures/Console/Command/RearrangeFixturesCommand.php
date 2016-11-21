<?php
namespace Fixtures\Console\Command;

use Fixtures\FixtureLoader;
use Fixtures\Generator;
use Fixtures\Rearrange;
use Fixtures\Repository\FolderRepository;
use Nelmio\Alice\Fixtures;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Object\Folder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class RearrangeFixturesCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('fixtures:rearrange')
            ->setDescription('Reorders yml fixtures');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rearranger = new Rearrange();
        $rearranger->rearrangeFixtures();

        $output->writeln('<info>Done. Your fixtures are at: "' . FixtureLoader::FIXTURE_FOLDER . '".</info>');

    }


}
