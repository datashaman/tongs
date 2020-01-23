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
use Symfony\Component\Finder\Finder;

class NoopPlugin extends Plugin
{
}

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
        $this->assertEquals($this->fixture('src'), $tongs->source());
    }

    public function testDefaultDestination()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue((bool) $tongs->destination());
        $this->assertEquals($this->fixture('build'), $tongs->destination());
    }

    public function testDefaultClean()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue($tongs->clean());
    }

    public function testUseAddPluginToStack()
    {
        $tongs = new Tongs($this->directory);
        $tongs->use(new NoopPlugin($tongs));
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
        $this->assertEquals($this->fixture('dir'), $tongs->source());
    }

    public function testSourceSetAbsolute()
    {
        $tongs = new Tongs($this->directory);
        $tongs->source('/dir');
        $this->assertEquals('/dir', $tongs->source());
    }

    public function testDestinationSet()
    {
        $tongs = new Tongs($this->directory);
        $tongs->destination('dir');
        $this->assertEquals($this->fixture('dir'), $tongs->destination());
    }

    public function testDestinationSetAbsolute()
    {
        $tongs = new Tongs($this->directory);
        $tongs->destination('/dir');
        $this->assertEquals('/dir', $tongs->destination());
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
            collect([
                "index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
            $tongs->read()
        );
    }

    public function testReadSymbolicLink()
    {
        $tongs = new Tongs($this->fixture('read-symbolic-link'));
        $this->assertEquals(
            collect([
                "dir/index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
            $tongs->read()
        );
    }

    public function testReadProvidedDirectory()
    {
        $tongs = new Tongs($this->fixture('read-dir'));
        $this->assertEquals(
            collect([
                "index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
            $tongs->read($this->fixture('read-dir/dir'))
        );
    }

    public function testReadMode()
    {
        $tongs = new Tongs($this->fixture('read-mode'));
        $this->assertEquals(
            collect([
                "bin" => [
                    "contents" => "echo test",
                    "mode" => "0755",
                ],
            ]),
            $tongs->read()
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
        $tongs->ignore('nested');
        $this->assertEquals(
            collect([
                "index.md" => [
                    "title" => "A Title",
                    "date" => new DateTime('2013-12-02'),
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
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
                "mode" => "0644",
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

    public function testWriteDirectory()
    {
        $tongs = new Tongs($this->fixture('write'));
        $files = [
            'index.md' => [
                'contents' => 'body',
            ],
        ];
        $tongs->write($files);
        $this->assertDirs($this->fixture('write/expected'), $this->fixture('write/build'));
    }

    protected function assertDirs(string $expected, string $actual)
    {
        $expected = (new Finder())
            ->files()
            ->followLinks()
            ->in($expected);

        $actual = (new Finder())
            ->files()
            ->followLinks()
            ->in($actual);

        $expected = collect($expected)
            ->mapWithKeys(
                function ($file) {
                    return [
                        $file->getRelativePathname() => [
                            'contents' => trim($file->getContents()),
                            'mode' => $file->getPerms(),
                        ],
                    ];
                }
            );

        $actual = collect($actual)
            ->mapWithKeys(
                function ($file) {
                    return [
                        $file->getRelativePathname() => [
                            'contents' => trim($file->getContents()),
                            'mode' => $file->getPerms(),
                        ],
                    ];
                }
            );

        $this->assertEquals($expected, $actual);
    }

    protected function fixture($path = null)
    {
        return "{$this->directory}/{$path}";
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
