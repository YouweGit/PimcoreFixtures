<?php
namespace Fixtures\Console\Command;

use Fixtures\FixtureLoader;
use Nelmio\Alice\Fixtures;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('fixtures:load')
            ->setDescription('Imports yml fixtures');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Currently we have calculated fields without any class attached
        $this->disableLogging();

        $fixturesFiles = FixtureLoader::getFixturesFiles();
        $progress = new ProgressBar($output, count($fixturesFiles));
        $progress->setProgressCharacter(" ");
        $progress->setEmptyBarCharacter(' ');
        $style = new OutputFormatterStyle('white', 'yellow', array('bold', 'blink'));
        $output->getFormatter()->setStyle('fire', $style);


        $progress->setBarCharacter("\xF0\x9F\x8D\xBA");
        $progress->setOverwrite(false);
        $progress->start();
        $progress->setFormat(" %current%/%max% [%bar%] <info>%percent:3s%% %elapsed:6s% %memory:6s%\t <comment>Loading</comment> %filename%</info>");

        $fixtureFiles = new FixtureLoader();
        foreach ($fixturesFiles as $fixtureFile) {
            $progress->setMessage(str_replace(PIMCORE_WEBSITE_VAR, '', $fixtureFile), 'filename');
            $fixtureFiles->load($fixtureFile);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('<info>Done</info>');
    }

}
