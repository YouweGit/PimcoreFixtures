<?php

namespace FixtureBundle\Command;

use FixtureBundle\Service\FixtureLoader;
use Pimcore\Console\AbstractCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('fixtures:load')
            ->setDescription('Imports yml fixtures')
            ->addOption('with-cache', 'c', InputArgument::OPTIONAL, 'Calculates the fingerprint of fixtures and if matches with load sql file instead of looping throw fixtures', false)
            ->addOption('omit-validation', 'ov', InputArgument::OPTIONAL, 'Omits object validation', true)
            ->addOption('check-path-exists', 'cpe', InputArgument::OPTIONAL, 'If true it will check if the path already exists and if yes then it will update the values', false)
            ->addOption('files', 'f', InputArgument::OPTIONAL, 'Comma separated files located at "' . FixtureLoader::FIXTURE_FOLDER . '"');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $withCache = $input->getOption('with-cache');
        $omitValidation = $input->getOption('omit-validation');
        $checkPathExists = $input->getOption('check-path-exists');
        $files = $input->getOption('files') ? explode(',', $input->getOption('files')) : null;
        $fixtureFiles = FixtureLoader::getFixturesFiles($files);
        $fingerPrintFilePath = PIMCORE_TEMPORARY_DIRECTORY . '/pimcore_fixtures_cache_' . $this->getSha1FromFixtures($fixtureFiles). '.sql';

        if ($withCache === false || file_exists($fingerPrintFilePath) === false) {
            $steps = $withCache ? count($fixtureFiles) + 1 : count($fixtureFiles);
            $progress = new ProgressBar($output, $steps);
            $progress->setProgressCharacter(' ');
            $progress->setEmptyBarCharacter(' ');
            $progress->setBarCharacter("\xF0\x9F\x8D\xBA"); // Beer
            $progress->setOverwrite(false);
            $progress->start();
            $progress->setFormat(" %current%/%max% [%bar%] <info>%percent:3s%% %elapsed:6s% %memory:6s%\t%message%</info>");

            $fixtureLoader = new FixtureLoader($checkPathExists, $omitValidation);
            foreach ($fixtureFiles as $fixtureFile) {
                $progress->setMessage('<comment>Loading</comment>  ' . str_replace(PIMCORE_WEBSITE_VAR, '', $fixtureFile));
                $progress->advance();
                $fixtureLoader->load($fixtureFile);
            }

            if ($withCache === true) {
                $progress->setMessage('<comment>Caching loaded data</comment>');
                $progress->advance();
                $this->cacheFixtures($fingerPrintFilePath);
            }
            $progress->setMessage('');
            $progress->finish();
            $progress->clear();
            $output->writeln('  ');

        } else {
            if (file_exists($fingerPrintFilePath)) {
                $output->writeln(' <info>Loading fixtures from cache</info>');
                $this->loadFromCache($fingerPrintFilePath);
            }
        }

    }


    private function getSha1FromFixtures($fixtureFiles)
    {
        $sha1sFromContent = '';
        foreach ($fixtureFiles as $fixtureFile) {
            if (is_file($fixtureFile) && is_readable($fixtureFile)) {
                $sha1sFromContent .= sha1_file($fixtureFile);
            }
        }

        return sha1($sha1sFromContent);
    }

    /**
     * @param $destination
     */
    private function cacheFixtures($destination)
    {
        $conf = Config::getSystemConfig(true);

        //Store the mysql credentials else mysql will complain

        $temp = $this->getTemporaryCredentialsFile();

        $metaData = stream_get_meta_data($temp);
        $tmpFilePath = $metaData['uri'];

        $dumpCommand = join(' ', [
            'mysqldump',
            '--defaults-file=' . $tmpFilePath,
            '--databases ' . $conf->database->params->dbname,
            '--port ' . $conf->database->params->port,
            '--no-autocommit',
            '--single-transaction',
            '> ' . $destination
        ]);

        system($dumpCommand);

        fclose($temp); // this removes the file


    }

    /**
     * @return resource
     */
    private function getTemporaryCredentialsFile()
    {
        $conf = Config::getSystemConfig(true);

        $temp = tmpfile();
        $credentials = join("\n", [
            '[client]',
            'user = ' . $conf->database->params->username,
            'password = ' . $conf->database->params->password,
            'host = ' . $conf->database->params->host
        ]);
        fwrite($temp, $credentials);

        return $temp;
    }

    /**
     * @param $filePath
     */
    private function loadFromCache($filePath)
    {
        $conf = Config::getSystemConfig(true);

        //Store the mysql credentials else mysql will complain

        $temp = $this->getTemporaryCredentialsFile();

        $metaData = stream_get_meta_data($temp);
        $tmpFilePath = $metaData['uri'];

        $mysqlLoadCommand = join(' ', [
            'mysql',
            '--defaults-file=' . $tmpFilePath,
            '--port ' . $conf->database->params->port,
            $conf->database->params->dbname,
            ' < ' . $filePath
        ]);

        system($mysqlLoadCommand);

        fclose($temp); // this removes the file
    }
}
