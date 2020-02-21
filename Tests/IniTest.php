<?php

declare(strict_types=1);

/*
 * This code is under BSD 3-Clause "New" or "Revised" License.
 *
 * PHP version 7 and above required
 *
 * @category  LoaderManager
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 Biurad Group (https://biurad.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * @link      https://www.biurad.com/projects/loadermanager
 * @since     Version 0.1
 */

namespace BiuradPHP\Loader\Tests;

use PHPUnit\Framework\TestCase;
use BiuradPHP\Loader\Adapters\IniAdapter;
use BiuradPHP\Loader\ConfigLoader;

/**
 * @requires PHP 7.1.30
 * @requires PHPUnit 7.5
 */
class IniTest extends TestCase
{
    private function getExpectedData()
    {
        return [
            'test' => [
            'dir' => [
                'root' => true,
                'lib' => null,
            ],
            'forbidden_file_extensions' => [
                'php',
                'php3',
                'pl',
                'com',
                'exe',
                'bat',
                'cgi',
                'htaccess'
            ],
            'debugger_token' => 'debug',
            ]
        ];
    }

    private function getActualData(): string
    {
        return <<<INI
; generated by BiuradPHP\Loader\Adapters\IniAdapter

[test]
dir.root = true
dir.lib = ""
forbidden_file_extensions.0 = "php"
forbidden_file_extensions.1 = "php3"
forbidden_file_extensions.2 = "pl"
forbidden_file_extensions.3 = "com"
forbidden_file_extensions.4 = "exe"
forbidden_file_extensions.5 = "bat"
forbidden_file_extensions.6 = "cgi"
forbidden_file_extensions.7 = "htaccess"
debugger_token = "debug"


INI;
    }

    public function testFromString()
    {
        $ini = new IniAdapter();
        $this->assertEquals($this->getExpectedData(), $ini->fromString($this->getActualData()));
    }

    public function testFromFile()
    {
        $ini = new IniAdapter();
        $this->assertEquals($this->getExpectedData(), $ini->fromFile(__DIR__.'/Fixtures/data/test4.ini'));

        $loader = new ConfigLoader();
        $this->assertEquals($this->getExpectedData(), $loader->loadFile(__DIR__.'/Fixtures/data/test4.ini'));
    }

    public function testDumpIniToArray()
    {
        $ini = new IniAdapter();
        $this->assertEquals($this->getActualData(), $ini->dump($this->getExpectedData()));
    }
}
