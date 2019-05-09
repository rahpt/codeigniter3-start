<?php

/**
 * Part of CodeIgniter Freshen Start
 *
 * @author     José Proença <https://github.com/RahPT>
 * @license    MIT License
 * @copyright  2019 José Proença
 * @link       https://github.com/rahpt/codeigniter3-start
 */

namespace RahPT\CodeIgniter;

use Composer\Script\Event;

class Installer {

    const DOCROOT = 'public';

    /**
     * Composer post install script
     *
     * @param Event $event
     */
    public static function postInstall(Event $event = null) {
        // Copy CodeIgniter files
        self::recursiveCopy('vendor/codeigniter/framework/application', 'application');
        mkdir(static::DOCROOT, 0755);
        copy('vendor/codeigniter/framework/index.php', static::DOCROOT . '/index.php');
        copy('src/dot.htaccess', static::DOCROOT . '/.htaccess');
        copy('vendor/codeigniter/framework/.gitignore', '.gitignore');

        // Fix paths in index.php
        $file = static::DOCROOT . '/index.php';
        self::updateParams($file,
                [
                    [
                        'from' => '$system_path = \'system\';',
                        'to' => '$system_path = \'../vendor/codeigniter/framework/system\';'
                    ],
                    [
                        'from' => '$application_folder = \'application\';',
                        'to' => '$application_folder = \'../application\';'
                    ]
                ]
        );

        // Enable Composer Autoloader
        $file = 'application/config/config.php';
        self::updateParams($file,
                [
                    [
                        'from' => '$config[\'composer_autoload\'] = FALSE;',
                        'to' => '$config[\'composer_autoload\'] = realpath(APPPATH . \'../vendor/autoload.php\');'
                    ]
                ],
                // Set 'index_page' blank
                [
                    'from' => '$config[\'index_page\'] = \'index.php\';',
                    'to' => '$config[\'index_page\'] = \'\';'
                ]
        );
        // Update composer.json
        copy('src/composer.json.dist', 'composer.json');
        // Run composer update
        self::composerUpdate();
        // Show message
        self::showMessage($event);
        // Delete unneeded files
        self::deleteSelf();
    }

    private static function updateParams($fileName, $arrParams) {
        $contents = file_get_contents($fileName);
        foreach ($arrParams as $param) {
            $contents = str_replace($param['from'], $param['to'], $contents);
        }
        file_put_contents($fileName, $contents);
    }

    private static function composerUpdate() {
        passthru('composer update');
    }

    /**
     * Composer post install script
     *
     * @param Event $event
     */
    private static function showMessage(Event $event = null) {
        $io = $event->getIO();
        $io->write('==================================================');
        $io->write('<info>`public/.htaccess` was installed. If you don\'t need it, please remove it.</info>');
        $io->write('<info>If you want to install translations for system messages or some third party libraries,</info>');
        $io->write('$ cd <codeigniter_project_folder>');
        $io->write('$ php bin/install.php');
        $io->write('<info>The above command will show help message.</info>');
        $io->write('See <https://github.com/rahpt/codeigniter-start> for details');
        $io->write('==================================================');
    }

    private static function deleteSelf() {
        unlink(__FILE__);
        rmdir('src');
        unlink('composer.json.dist');
        unlink('dot.htaccess');
        unlink('LICENSE.md');
    }

    /**
     * Recursive Copy
     *
     * @param string $src
     * @param string $dst
     */
    private static function recursiveCopy($src, $dst) {
        mkdir($dst, 0755);

        $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                mkdir($dst . '/' . $iterator->getSubPathName());
            } else {
                copy($file, $dst . '/' . $iterator->getSubPathName());
            }
        }
    }

}

