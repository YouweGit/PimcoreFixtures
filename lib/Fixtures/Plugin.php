<?php

namespace Fixtures;

use Fixtures\Console\Command\GenerateFixturesCommand;
use Fixtures\Console\Command\LoadFixturesCommand;
use Fixtures\Console\Command\RearrangeFixturesCommand;
use Fixtures\Console\Command\RemoverCommand;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Console\ConsoleCommandPluginTrait;
use Symfony\Component\Console\Command\Command;


class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    use ConsoleCommandPluginTrait;

    public static function install()
    {
        return true;
    }

    public static function uninstall()
    {
        return true;
    }

    public static function isInstalled()
    {
        return true;
    }

    public function init()
    {
        $this->initConsoleCommands();
    }

    /**
     * Returns an array of commands to be added to the application.
     * To be implemented by plugin classes providing console commands.
     *
     * @return Command[]
     */
    public function getConsoleCommands()
    {
        return [
            new LoadFixturesCommand(),
            new GenerateFixturesCommand(),
            new RemoverCommand(),
            new RearrangeFixturesCommand()
        ];
    }
}
