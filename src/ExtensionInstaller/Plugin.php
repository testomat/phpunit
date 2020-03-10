<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/testomat/phpunit
 */

namespace Testomat\PHPUnit\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class Plugin implements EventSubscriberInterface, PluginInterface
{
    /**
     * Check if the the plugin is activated.
     *
     * @var bool
     */
    private static $activated = true;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        if (($errorMessage = $this->getErrorMessage($io)) !== null) {
            self::$activated = false;

            $io->writeError('<warning>Testomat PHPUnit plugin has been disabled. ' . $errorMessage . '</warning>');

            return;
        }

        // to avoid issues when phpunit plugin is upgraded, we load all PHP classes now
        // that way, we are sure to use all classes from the same version.
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(\dirname(__DIR__, 1), FilesystemIterator::SKIP_DOTS)) as $file) {
            /** @var SplFileInfo $file */
            if (substr($file->getFilename(), -4) === '.php') {
                class_exists(__NAMESPACE__ . str_replace('/', '\\', substr($file->getFilename(), \strlen(__DIR__), -4)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        if (! self::$activated) {
            return [];
        }

        return [
            ScriptEvents::POST_INSTALL_CMD => 'process',
            ScriptEvents::POST_UPDATE_CMD => 'process',
        ];
    }

    public function process(Event $event): void
    {
    }

    /**
     * Check if plugin can be activated.
     */
    private function getErrorMessage(IOInterface $io): ?string
    {
        return null;
    }
}
