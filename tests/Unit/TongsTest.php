<?php

declare(strict_types=1);

namespace Datashaman\Tests\Unit;

use Datashaman\Tongs\Tests\TestCase;
use Datashaman\Tongs\Plugins\Plugin;
use Datashaman\Tongs\Tongs;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

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
        $this->tongs = new Tongs($this->directory);
    }

    public function testDefaultSource()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue((bool) $tongs->source());
        $this->assertEquals($this->directory('src'), $tongs->source());
    }

    public function testDefaultDestination()
    {
        $tongs = new Tongs($this->directory);
        $this->assertTrue((bool) $tongs->destination());
        $this->assertEquals($this->directory('build'), $tongs->destination());
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
        $this->assertEquals($this->directory('dir'), $tongs->source());
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
        $this->assertEquals($this->directory('dir'), $tongs->destination());
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
        $this->assertEquals($this->directory('dir'), $tongs->path('dir'));
    }

    public function testReadSourceDirectory()
    {
        $tongs = new Tongs($this->directory('read'));
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
        $tongs = new Tongs($this->directory('read-symbolic-link'));
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
        $tongs = new Tongs($this->directory('read-dir'));
        $this->assertEquals(
            collect([
                "index.md" => [
                    "title" => "A Title",
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
            $tongs->read($this->directory('read-dir/dir'))
        );
    }

    public function testReadMode()
    {
        $tongs = new Tongs($this->directory('read-mode'));
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
        $tongs = new Tongs($this->directory('read-frontmatter'));
        $tongs->frontmatter(false);
        $files = $tongs->read();
        $this->assertFalse(Arr::has($files['index.md'], 'thing'));
    }

    public function testReadIgnoreFiles()
    {
        $tongs = new Tongs($this->directory('basic'));
        $tongs->ignore('nested');
        $this->assertEquals(
            collect([
                "index.md" => [
                    "title" => "A Title",
                    "date" => Carbon::parse('2013-12-02'),
                    "contents" => "body",
                    "mode" => "0644",
                ],
            ]),
            $tongs->read()
        );
    }

    protected function directory($path = null)
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
