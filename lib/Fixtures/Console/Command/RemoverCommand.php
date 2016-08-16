<?php
namespace Fixtures\Console\Command;

use Fixtures\FixtureLoader;
use Nelmio\Alice\Fixtures;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Pimcore\Model\Object\AbstractObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoverCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('fixtures:delete-path')
            ->setDescription('Deletes object/asset/document by path')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Element type object, asset or document ', 'object')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Full path to the element', '/')
            ->addOption('onlyChildren', 'c', InputOption::VALUE_REQUIRED, 'Defile if only children elements should be deleted', 'yes')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Set this parameter to execute this action');
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


        $type = $input->getOption('type');
        $path = $input->getOption('path');
        $onlyChildren = $input->getOption('onlyChildren');
        $this->validateOptions($type, $path, $onlyChildren);

        if (!$input->getOption('force')) {
            $confirmationQuestion = $this->askForConfiration($output, $type, $path, $onlyChildren);

            if (!$helper->ask($input, $output, $confirmationQuestion)) {
                return;
            }
        }

        /** @var AbstractObject|Document|Asset $element */
        $element = Service::getElementByPath($type, $path);


        if ($onlyChildren === 'yes') {
            $children = $element->getChilds();
            $progress = $this->getProgressBar($output, count($children));
            /** @var AbstractObject|Document $child */
            foreach ($children as $child) {
                $progress->setMessage($child->getFullPath(), 'path');
                $child->delete();
                $progress->advance();
            }
        } else {

            $progress = $this->getProgressBar($output,1);
            $progress->setMessage($element->getFullPath(), 'path');
            $element->delete();

            $progress->advance();
        }
        $progress->finish();

        $output->writeln("<info>Done deleting $path</info>");

    }

    /**
     * @param string $type
     * @param string $path
     * @param string $onlyChildren
     * @return ElementInterface
     */
    private function validateOptions($type, $path, $onlyChildren)
    {
        if (!in_array($type, ['object', 'asset', 'document'])) {
            throw new \RuntimeException('Only object, asset or document is allowed as type');
        }

        $element = Service::getElementByPath($type, $path);
        if (!$element) {
            throw new \RuntimeException("Could not find '$type' at path '$path'");
        }

        if (!in_array($onlyChildren, ['yes', 'no'])) {
            throw new \RuntimeException('Only yes/no is allowed as onlyChildren');
        }

        if ($onlyChildren === 'yes' && method_exists($element, 'getChilds') === false) {
            $className = get_class($element);
            throw new \RuntimeException("Only children is '$onlyChildren' but '$className' doesn't have a 'getChilds' method");
        }
    }

    /**
     * @param OutputInterface $output
     * @param $type
     * @param $path
     * @param $onlyChildren
     * @return ConfirmationQuestion
     */
    protected function askForConfiration(OutputInterface $output, $type, $path, $onlyChildren)
    {
        $output->writeln(
            ['<info>',
                'Are you sure you want to continue: ',
                'Type: <comment>' . $type . '</comment>',
                'Path: <comment>' . $path . '</comment>',
                'Only children: <comment>' . $onlyChildren . '</comment>',
                '</info>'
            ]);

        $confirmationQuestion = new ConfirmationQuestion(
            '<info>Continue with this action? (y)</info>'
        );
        return $confirmationQuestion;
    }

    /**
     * @param OutputInterface $output
     * @param int $max
     * @return ProgressBar
     */
    protected function getProgressBar(OutputInterface $output, $max)
    {
        $progress = new ProgressBar($output,$max);
//        $progress->setOverwrite(false);
        $progress->start();
        $progress->setFormat(" %current%/%max% [%bar%] \t Deleting %path%");
        return $progress;
    }
}
