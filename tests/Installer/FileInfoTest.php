<?php

namespace Octopy\DDD\Tests\Installer;

use Octopy\DDD\Installer\FileInfo\NewFileInfo;
use Octopy\DDD\Installer\FileInfo\OriFileInfo;
use Octopy\DDD\Installer\Installer;
use Octopy\DDD\Tests\TestCase;

class FileInfoTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->installer = new Installer($this->app->setBasePath(
            __DIR__ . '/../Laravel'
        ));
    }

    /**
     * @return void
     */
    public function testOriToNew() : void
    {
        $file = new OriFileInfo(
            $this->app, __DIR__ . '/../Laravel/app/Http/Kernel.php'
        );

        $this->assertSame(__DIR__ . '/../Laravel/app/Http/Kernel.php', $file->getOriFileInfo()->getPathname());
        $this->assertSame(__DIR__ . '/../Laravel/app/Infrastructure/Core/Http/Kernel.php', $file->getNewFileInfo()->getPathname());

        $this->assertSame('App\Http', $file->getOriNameSpace());
        $this->assertSame('App\Http\Kernel', $file->getOriClassName());
        $this->assertSame('App\Infrastructure\Core\Http', $file->getNewNameSpace());
        $this->assertSame('App\Infrastructure\Core\Http\Kernel', $file->getNewClassName());
    }

    /**
     * @return void
     */
    public function testNewToOri() : void
    {
        $file = new NewFileInfo(
            $this->app, __DIR__ . '/../Laravel/app/Infrastructure/Core/Http/Kernel.php'
        );

        $this->assertSame(__DIR__ . '/../Laravel/app/Http/Kernel.php', $file->getNewFileInfo()->getPathname());
        $this->assertSame(__DIR__ . '/../Laravel/app/Infrastructure/Core/Http/Kernel.php', $file->getOriFileInfo()->getPathname());

        $this->assertSame('App\Http', $file->getNewNameSpace());
        $this->assertSame('App\Http\Kernel', $file->getNewClassName());
        $this->assertSame('App\Infrastructure\Core\Http', $file->getOriNameSpace());
        $this->assertSame('App\Infrastructure\Core\Http\Kernel', $file->getOriClassName());
    }
}
