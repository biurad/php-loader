<?php declare(strict_types=1);

/*
 * This file is part of Biurad opensource projects.
 *
 * @copyright 2022 Biurad Group (https://biurad.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Biurad\Loader\Tests;

use Biurad\Loader\Files\Adapters;
use Biurad\Loader\Files\RecursiveUniformResourceIterator;
use Biurad\Loader\Files\UniformResourceIterator;
use Biurad\Loader\Locators;
use PHPUnit\Framework as t;

beforeAll(function (): void {
    require_once __DIR__.'/Fixtures/Hello.php';
});

dataset('data_expected', [
    'test_array_data' => [
        [
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
                    'htaccess',
                ],
                'debugger_token' => 'debug',
            ],
        ],
    ],
]);

dataset('ini_actual', [
'ini_data' => <<<INI
; generated by Biurad\Loader\Files\Adapters\IniFileAdapter

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


INI
]);

dataset('json_actual', [
'json_data' => <<<JSON
{
    "test": {
        "dir": {
            "root": true,
            "lib": null
        },
        "forbidden_file_extensions": [
            "php",
            "php3",
            "pl",
            "com",
            "exe",
            "bat",
            "cgi",
            "htaccess"
        ],
        "debugger_token": "debug"
    }
}
JSON
]);

dataset('yaml_actual', [
'yaml_data' => <<<YAML
test:
    dir:
        root: true
        lib: ~
    forbidden_file_extensions:
        - php
        - php3
        - pl
        - com
        - exe
        - bat
        - cgi
        - htaccess
    debugger_token: debug

YAML
]);

dataset('neon_actual', [
'neon_data' => <<<NEON
# generated by Biurad\Loader\Files\Adapters\NeonFileAdapter

test:
  dir:
    root: true
    lib: null

  forbidden_file_extensions:
    - php
    - php3
    - pl
    - com
    - exe
    - bat
    - cgi
    - htaccess

  debugger_token: debug


NEON
 ]);

dataset('xml_actual', [
'xml_data' => <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <test>
        <dir root="true" lib="null"/>
        <forbidden_file_extensions>php</forbidden_file_extensions>
        <forbidden_file_extensions>php3</forbidden_file_extensions>
        <forbidden_file_extensions>pl</forbidden_file_extensions>
        <forbidden_file_extensions>com</forbidden_file_extensions>
        <forbidden_file_extensions>exe</forbidden_file_extensions>
        <forbidden_file_extensions>bat</forbidden_file_extensions>
        <forbidden_file_extensions>cgi</forbidden_file_extensions>
        <forbidden_file_extensions>htaccess</forbidden_file_extensions>
        <debugger_token>debug</debugger_token>
    </test>
</config>

XML
]);

dataset('uniform_uri', [
    'UniformResourceLocator' => new Locators\UniformResourceLocator(__DIR__.'/Fixtures'),
]);

dataset('resource_paths_add', [
    ['base', 'base'],
    ['local', 'local'],
    ['override', 'override'],
    ['all', ['override://all', 'local://all', 'base://all']],
]);

dataset('resource_streams', [
    ['base', ['base']],
    ['local', ['local']],
    ['override', ['override']],
    ['all', ['override://all', 'local://all', 'base://all']],
    ['fail', []],
]);

dataset('resource_paths', [
    ['all://base.txt', ['base/all/base.txt']],
    ['all://base_all.txt', ['override/all/base_all.txt', 'local/all/base_all.txt', 'base/all/base_all.txt']],
    ['all://base_local.txt', ['local/all/base_local.txt', 'base/all/base_local.txt']],
    ['all://base_override.txt', ['override/all/base_override.txt', 'base/all/base_override.txt']],
    ['all://local.txt', ['local/all/local.txt']],
    ['all://local_override.txt', ['override/all/local_override.txt', 'local/all/local_override.txt']],
    ['all://override.txt', ['override/all/override.txt']],
    ['all://asdf/../base.txt', ['base/all/base.txt']],
]);

dataset('normalize_paths', [
    ['', '/'],
    ['./', ''],
    ['././/./', ''],
    ['././/../', false],
    ['/', '/'],
    ['//', '/'],
    ['///', '/'],
    ['/././', '/'],
    ['foo', 'foo'],
    ['/foo', '/foo'],
    ['//foo', '/foo'],
    ['/foo/', '/foo/'],
    ['//foo//', '/foo/'],
    ['path/to/file.txt', 'path/to/file.txt'],
    ['path/to/../file.txt', 'path/file.txt'],
    ['path/to/../../file.txt', 'file.txt'],
    ['path/to/../../../file.txt', null],
    ['/path/to/file.txt', '/path/to/file.txt'],
    ['/path/to/../file.txt', '/path/file.txt'],
    ['/path/to/../../file.txt', '/file.txt'],
    ['/path/to/../../../file.txt', null],
    ['c:\\', 'c:/'],
    ['c:\\path\\to\file.txt', 'c:/path/to/file.txt'],
    ['c:\\path\\to\../file.txt', 'c:/path/file.txt'],
    ['c:\\path\\to\../../file.txt', 'c:/file.txt'],
    ['c:\\path\\to\../../../file.txt', null],
    ['stream://path/to/file.txt', 'stream://path/to/file.txt'],
    ['stream://path/to/../file.txt', 'stream://path/file.txt'],
    ['stream://path/to/../../file.txt', 'stream://file.txt'],
    ['stream://path/to/../../../file.txt', null],
]);

test('if namespace or classes can be aliased', function (): void {
    $loader = new Locators\AliasLocator(['Zzz\\' => 'Biurad\\']);
    $loader->add(Fixtures\Hello::class, 'SpaceX');
    $loader->register();

    $hello = new \SpaceX();
    t\assertInstanceOf(Fixtures\Hello::class, $hello);
    t\assertTrue(\class_exists("Zzz\Loader\Tests\Fixtures\Hello"));
});

test('if classes can be found from a path', function (): void {
    $loader = (new Locators\FileLocator([__DIR__.'/../src']))->getClassLocator();

    t\assertEquals([
        'Biurad\Loader\Files\Adapters\XmlFileAdapter',
        'Biurad\Loader\Files\Adapters\YamlFileAdapter',
        'Biurad\Loader\Files\Adapters\NeonFileAdapter',
        'Biurad\Loader\Files\Adapters\MoFileAdapter',
        'Biurad\Loader\Files\Adapters\CsvFileAdapter',
        'Biurad\Loader\Files\Adapters\IniFileAdapter',
        'Biurad\Loader\Files\Adapters\AbstractAdapter',
        'Biurad\Loader\Files\Adapters\PhpFileAdapter',
        'Biurad\Loader\Files\Adapters\JsonFileAdapter',
    ], \array_keys(\iterator_to_array($loader->getClasses(Adapters\AbstractAdapter::class))));

    t\assertEquals([
        'Biurad\Loader\Locators\FileLocator',
        'Biurad\Loader\Locators\UniformResourceLocator',
        'Biurad\Loader\Locators\ConfigLocator',
        'Biurad\Loader\Locators\ClassLocator',
        'Biurad\Loader\Locators\AliasLocator',
        'Biurad\Loader\Exceptions\FileGeneratingException',
        'Biurad\Loader\Exceptions\FileLoadingException',
        'Biurad\Loader\Exceptions\FileNotFoundException',
        'Biurad\Loader\Exceptions\LoaderException',
        'Biurad\Loader\Files\UniformResourceIterator',
        'Biurad\Loader\Files\RecursiveUniformResourceIterator',
        'Biurad\Loader\Files\Adapters\XmlFileAdapter',
        'Biurad\Loader\Files\Adapters\YamlFileAdapter',
        'Biurad\Loader\Files\Adapters\NeonFileAdapter',
        'Biurad\Loader\Files\Adapters\MoFileAdapter',
        'Biurad\Loader\Files\Adapters\CsvFileAdapter',
        'Biurad\Loader\Files\Adapters\IniFileAdapter',
        'Biurad\Loader\Files\Adapters\AbstractAdapter',
        'Biurad\Loader\Files\Adapters\PhpFileAdapter',
        'Biurad\Loader\Files\Adapters\JsonFileAdapter',
    ], \array_keys(\iterator_to_array($loader->getClasses())));
});

test('if ini can be loaded from string', function (array $expected, string $actual): void {
    $ini = new Adapters\IniFileAdapter();
    t\assertEquals($expected, $ini->fromString($actual));
})->with('data_expected', 'ini_actual');

test('if ini can be loaded from file', function (array $expected): void {
    $ini = new Adapters\IniFileAdapter();
    t\assertEquals($expected, $ini->fromFile(__DIR__.'/Fixtures/data/test4.ini'));

    $loader = new Locators\ConfigLocator();
    t\assertEquals($expected, $loader->loadFile(__DIR__.'/Fixtures/data/test4.ini'));
})->with('data_expected');

test('if ini can be dumped from array to string', function (array $expected, string $actual): void {
    $ini = new Adapters\IniFileAdapter();
    t\assertEquals($actual, $ini->dump($expected));
})->with('data_expected', 'ini_actual');

test('if json can be loaded from string', function (array $expected, string $actual): void {
    $json = new Adapters\JsonFileAdapter();
    t\assertEquals($expected, $json->fromString($actual));
})->with('data_expected', 'json_actual');

test('if json can be loaded from file', function (array $expected): void {
    $json = new Adapters\JsonFileAdapter();
    t\assertEquals($expected, $json->fromFile(__DIR__.'/Fixtures/data/test3.json'));

    $loader = new Locators\ConfigLocator();
    t\assertEquals($expected, $loader->loadFile(__DIR__.'/Fixtures/data/test3.json'));
})->with('data_expected');

test('if json can be dumped from array to string', function (array $expected, string $actual): void {
    $json = new Adapters\JsonFileAdapter();
    t\assertEquals($actual, $json->dump($expected));
})->with('data_expected', 'json_actual');

test('if yaml can be loaded from string', function (array $expected, string $actual): void {
    $yaml = new Adapters\YamlFileAdapter();
    t\assertEquals($expected, $yaml->fromString($actual));
})->with('data_expected', 'yaml_actual');

test('if yaml can be loaded from file', function (array $expected): void {
    $yaml = new Adapters\YamlFileAdapter();
    t\assertEquals($expected, $yaml->fromFile(__DIR__.'/Fixtures/data/test3.json'));

    $loader = new Locators\ConfigLocator();
    t\assertEquals($expected, $loader->loadFile(__DIR__.'/Fixtures/data/test2.yaml'));
})->with('data_expected');

test('if yaml can be dumped from array to string', function (array $expected, string $actual): void {
    $yaml = new Adapters\YamlFileAdapter();
    t\assertEquals(<<<YAML
test:
  dir: { root: true, lib: null }
  forbidden_file_extensions: [php, php3, pl, com, exe, bat, cgi, htaccess]
  debugger_token: debug

YAML, $yaml->dump($expected));
})->with('data_expected', 'neon_actual');

test('if neon can be loaded from string', function (array $expected, string $actual): void {
    $neon = new Adapters\NeonFileAdapter();
    t\assertEquals($expected, $neon->fromString($actual));
})->with('data_expected', 'neon_actual');

test('if neon can be loaded from file', function (array $expected): void {
    $neon = new Adapters\NeonFileAdapter();
    t\assertEquals($expected, $neon->fromFile(__DIR__.'/Fixtures/data/test7.neon'));

    $loader = new Locators\ConfigLocator();
    t\assertEquals($expected, $loader->loadFile(__DIR__.'/Fixtures/data/test7.neon'));
})->with('data_expected');

test('if neon can be dumped from array to string', function (array $expected, string $actual): void {
    $neon = new Adapters\NeonFileAdapter();
    t\assertEquals($actual, $neon->dump($expected));
})->with('data_expected', 'neon_actual');

test('if xml can be loaded from string', function (array $expected, string $actual): void {
    $xml = new Adapters\XmlFileAdapter();
    t\assertEquals($expected, $xml->fromString($actual));
})->with('data_expected', 'xml_actual');

test('if xml can be loaded from file', function (array $expected): void {
    $xml = new Adapters\XmlFileAdapter();
    t\assertEquals($expected, $xml->fromFile(__DIR__.'/Fixtures/data/test5.xml'));

    $loader = new Locators\ConfigLocator();
    t\assertEquals($expected, $loader->loadFile(__DIR__.'/Fixtures/data/test5.xml'));
})->with('data_expected');

test('if xml can be dumped from array to string', function (array $expected, string $actual): void {
    $xml = new Adapters\XmlFileAdapter();
    t\assertEquals($actual, $xml->dump($expected));
})->with('data_expected', 'xml_actual');

test('if resource locator base path exists', function (Locators\UniformResourceLocator $u): void {
    t\assertEquals(__DIR__.'/Fixtures', $u->getBase());
})->with('uniform_uri');

test('if resource locator paths can be streamed', function (Locators\UniformResourceLocator $u, string $scheme, string|array $lookup): void {
    t\assertFalse($u->schemeExists($scheme));
    $u->addPath($scheme, $lookup);
    t\assertTrue($u->schemeExists($scheme));
})->with('uniform_uri', 'resource_paths_add');

test('if resource locator has stream paths', function (Locators\UniformResourceLocator $u): void {
    t\assertFalse($u->schemeExists('foo'));
    t\assertFalse($u->schemeExists('file'));
    t\assertEquals(['base', 'local', 'override', 'all'], $u->getSchemes());
})->with('uniform_uri');

test('if resource locator paths exists in stream', function (Locators\UniformResourceLocator $u, string $scheme, array $expected): void {
    t\assertSame($expected, $u->getPaths($scheme));
})->with('uniform_uri', 'resource_streams');

test('if resource locator stream can be iterated', function (Locators\UniformResourceLocator $u): void {
    t\assertInstanceOf(UniformResourceIterator::class, $a = $u->getIterator('all://'));
    t\assertInstanceOf(RecursiveUniformResourceIterator::class, $b = $u->getRecursiveIterator('all://'));

    t\assertSame('file', $a->getType());
    t\assertSame('all://base_all.txt', $a->getUrl());
    t\assertFalse($b->hasChildren());
    t\assertCount(7, $a);
    t\assertEquals(\array_keys(\iterator_to_array($b)), \array_keys(\iterator_to_array($a)));
})->with('uniform_uri');

test('if resource locator can find resources', function (Locators\UniformResourceLocator $u, string $uri, array $paths): void {
    t\assertEquals($paths, $u->findResources($uri, false));
})->with('uniform_uri', 'resource_paths');

test('if resource locator can find individual resource', function (Locators\UniformResourceLocator $u, string $uri, array $paths): void {
    $path = $paths ? \reset($paths) : false;
    $fullPath = !$path ? false : __DIR__."/Fixtures/{$path}";

    $this->assertEquals(\str_replace('\\', '/', $fullPath), $u->findResource($uri));
    $this->assertEquals(\str_replace('\\', '/', $path), $u->findResource($uri, false));
})->with('uniform_uri', 'resource_paths');

test('if resource locator can normalize paths', function (Locators\UniformResourceLocator $u, string $uri, ?string $path): void {
    t\assertEquals($path, $u->normalize($uri));
})->with('uniform_uri', 'normalize_paths');