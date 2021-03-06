<?php

declare(strict_types=1);

namespace Datashaman\Tests\Unit;

use Datashaman\Tongs\Tests\TestCase;
use Datashaman\Tongs\Plugins\Plugin;
use Datashaman\Tongs\Tongs;
use DateTime;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class TongsTest extends TestCase
{
    /**
     * @var string
     */
    protected $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = realpath(__DIR__ . '/../fixtures');
    }

    public function testDefaultSource()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue((bool) $tongs->source());
        $this->assertEquals(
            realpath($this->fixture('src')),
            realpath($tongs->source()->path(''))
        );
    }

    public function testDefaultDestination()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue((bool) $tongs->destination());
        $this->assertEquals(
            realpath($this->fixture('build')),
            realpath($tongs->destination()->path(''))
        );
    }

    public function testDefaultClean()
    {
        $tongs = new Tongs($this->directory);
        $this->assertFalse($tongs->clean());
    }

    public function testUseAddPluginToStack()
    {
        $tongs = new Tongs($this->directory);
        $plugin = new class () extends Plugin {
        };
        $tongs->use($plugin);
        $this->assertCount(1, $tongs->plugins());
    }

    public function testIgnoreAddStringToList()
    {
        $tongs = new Tongs($this->directory);
        $tongs->ignore('dirfile');
        $this->assertCount(1, $tongs->ignore());
    }

    public function testIgnoreAddArrayToList()
    {
        $tongs = new Tongs($this->directory);
        $tongs->ignore(['dir1', 'dir2']);
        $this->assertCount(2, $tongs->ignore());
    }

    public function testIgnoreReturnList()
    {
        $tongs = new Tongs($this->directory);
        $tongs->ignore('dirfile');
        $this->assertEquals(['dirfile'], $tongs->ignore());
    }

    public function testDirectorySet()
    {
        $this->withTempDirectory(
            function ($directory) {
                $tongs = new Tongs($this->directory);
                $tongs->directory($directory);
                $this->assertEquals($directory, $tongs->directory());
            }
        );
    }

    public function testSourceSet()
    {
        $tongs = new Tongs($this->directory);
        $tongs->source('dir');
        $this->assertEquals(
            rtrim($this->fixture('dir'), '/'),
            rtrim($tongs->source()->path(''), '/')
        );
    }

    public function testSourceSetAbsolute()
    {
        $tongs = new Tongs($this->directory);
        $tongs->source('/tmp');
        $this->assertEquals(
            realpath('/tmp'),
            realpath($tongs->source()->path(''))
        );
    }

    public function testDestinationSet()
    {
        $tongs = new Tongs($this->directory);
        $tongs->destination('dir');
        $this->assertEquals(
            realpath($this->fixture('dir')),
            realpath($tongs->destination()->path(''))
        );
    }

    public function testDestinationSetAbsolute()
    {
        $tongs = new Tongs($this->directory);
        $tongs->destination('/tmp');
        $this->assertEquals(
            realpath('/tmp'),
            realpath($tongs->destination()->path(''))
        );
    }

    public function testCleanSet()
    {
        $tongs = new Tongs($this->directory);
        $tongs->clean(false);
        $this->assertFalse($tongs->clean());
    }

    public function testFrontmatterSet()
    {
        $tongs = new Tongs($this->directory);
        $tongs->frontmatter(false);
        $this->assertFalse($tongs->frontmatter());
    }

    public function testMetadataGet()
    {
        $tongs = new Tongs($this->directory);
        $this->assertEquals([], $tongs->metadata());
    }

    public function testMetadataSet()
    {
        $data = [
            'property' => true,
        ];

        $tongs = new Tongs($this->directory);
        $tongs->metadata($data);
        $this->assertEquals($data, $tongs->metadata());
    }

    public function testPath()
    {
        $tongs = new Tongs($this->directory);
        $this->assertEquals($this->fixture('dir'), $tongs->path('dir'));
    }

    public function testReadSourceDirectory()
    {
        $tongs = new Tongs($this->fixture('read'));
        $this->assertEquals(
            [
                "index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                ],
            ],
            $tongs->read()
        );
    }

    public function testReadProvidedDirectory()
    {
        $tongs = new Tongs($this->fixture('read-dir'));
        $actual = $tongs->read('dir');
        $this->assertEquals(
            [
                "dir/index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                ],
            ],
            $actual
        );
    }

    public function testReadFrontmatter()
    {
        $tongs = new Tongs($this->fixture('read-frontmatter'));
        $tongs->frontmatter(false);
        $files = $tongs->read();
        $this->assertFalse(Arr::has($files['index.md'], 'thing'));
    }

    public function testReadIgnoreFiles()
    {
        $tongs = new Tongs($this->fixture('basic'));
        $tongs->ignore('nested*');
        $this->assertEquals(
            [
                "index.md" => [
                    "title" => "A Title",
                    "date" => new DateTime('2013-12-02'),
                    "contents" => "body",
                ],
            ],
            $tongs->read()
        );
    }

    public function testReadFileRelative()
    {
        $tongs = new Tongs($this->fixture('read'));
        $this->assertEquals(
            [
                "title" => "A Title",
                "contents" => "body",
            ],
            $tongs->readFile('index.md')
        );
    }

    public function testReadInvalidFrontmatter()
    {
        $tongs = new Tongs($this->fixture('read-invalid-frontmatter'));
        $tongs->frontmatter(true);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid frontmatter');
        $file = $tongs->readFile('index.md');
    }

    public function testWriteDestinationDirectory()
    {
        $tongs = new Tongs($this->fixture('write'));
        $files = [
            'index.md' => [
                'contents' => 'body',
            ],
        ];
        $tongs->write($files);
        $this->assertEqualDirectories($this->fixture('write/expected'), $this->fixture('write/build'));
    }

    public function testWriteProvidedDirectory()
    {
        $tongs = new Tongs($this->fixture('write-dir'));
        $files = [
            'index.md' => [
                'contents' => 'body',
            ],
        ];
        $dir = $this->fixture('write-dir/out');
        $tongs->write($files, $dir);
        $this->assertEqualDirectories($this->fixture('write-dir/expected'), $this->fixture('write-dir/out'));
    }

    public function testWriteFile()
    {
        $tongs = new Tongs($this->fixture('write-file'));
        $file = 'index.md';
        $data = [
            'contents' => 'body',
        ];

        $tongs->writeFile($file, $data);

        $this->assertEqualDirectories(
            $this->fixture('write-file/expected'),
            $this->fixture('write-file/build')
        );

        $this->assertEquals(
            trim(file_get_contents($this->fixture('write-file/expected/index.md'))),
            trim(file_get_contents($this->fixture('write-file/build/index.md')))
        );
    }

    public function testRunProvidedPlugin()
    {
        $tongs = new Tongs($this->fixture());

        $plugin = new class () extends Plugin {
            public function handle(array $files, callable $next): array
            {
                assert($files['one'] == 'one');
                $files['two'] = 'two';

                return $next($files);
            }
        };

        $files = $tongs->run(
            [
                'one' => 'one',
            ],
            [
                $plugin,
            ]
        );

        $this->assertEquals('one', $files['one']);
        $this->assertEquals('two', $files['two']);
    }

    public function testProcessNoPlugins()
    {
        $tongs = new Tongs($this->fixture('basic'));
        $files = $tongs->process();
        $this->assertIsArray($files['index.md']);
        $this->assertEquals('A Title', $files['index.md']['title']);
        $this->assertIsArray($files['nested/index.md']);
    }

    public function testProcessBasicPlugin()
    {
        $tongs = new Tongs($this->fixture('basic-plugin'));

        $plugin = new class () extends Plugin {
            public function handle(array $files, callable $next): array
            {
                foreach($files as &$file) {
                    $file['contents'] = $file['title'];
                }

                return $next($files);
            }
        };

        $files = $tongs->use($plugin)->process();

        $this->assertCount(2, $files);
        $this->assertIsArray($files['one.md']);
        $this->assertEquals('one', $files['one.md']['title']);
        $this->assertEquals('one', $files['one.md']['contents']);
        $this->assertIsArray($files['two.md']);
        $this->assertEquals('two', $files['two.md']['title']);
        $this->assertEquals('two', $files['two.md']['contents']);
    }

    public function testBuildNoPlugins()
    {
        $tongs = new Tongs($this->fixture('basic'));
        $files = $tongs->build();
        $this->assertEqualDirectories($this->fixture('basic/expected'), $this->fixture('basic/build'));
    }

    public function testBuildBinaryFiles()
    {
        $tongs = new Tongs($this->fixture('basic-images'));
        $files = $tongs->build();
        $this->assertEqualDirectories($this->fixture('basic-images/expected'), $this->fixture('basic-images/build'));
    }

    public function testBuildBasicPlugin()
    {
        $tongs = new Tongs($this->fixture('basic-plugin'));

        $plugin = new class () extends Plugin {
            public function handle(array $files, callable $next): array
            {
                foreach ($files as &$file) {
                    $file['contents'] = $file['title'];
                }

                return $next($files);
            }
        };

        $files = $tongs->use($plugin)->build();
        $this->assertEqualDirectories($this->fixture('basic-plugin/expected'), $this->fixture('basic-plugin/build'));
    }

    protected function withTempDirectory(callable $callable)
    {
        $directory = tempnam(sys_get_temp_dir(), '');
        unlink($directory);
        File::makeDirectory($directory);
        $callable($directory);
        File::deleteDirectory($directory);
    }
}
