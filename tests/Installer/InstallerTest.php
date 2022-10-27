<?php

namespace Octopy\DDD\Tests\Installer;

use FilesystemIterator;
use Octopy\DDD\Installer\FileInfo\OriFileInfo;
use Octopy\DDD\Installer\Installer;
use Octopy\DDD\Tests\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class InstallerTest extends TestCase
{
    /**
     * @var Installer
     */
    protected Installer $installer;

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
    protected function tearDown() : void
    {
        $this->installer->restoreOriginalStructure();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testCreateMandatoryStructure() : void
    {
        $this->installer->createMandatoryStructure();

        $this->assertDirectoryExists($this->app->path(
            'Domain'
        ));

        $this->assertDirectoryExists($this->app->path(
            'Application'
        ));

        $this->assertDirectoryExists($this->app->path(
            'Infrastructure'
        ));
    }

    /**
     * @return void
     */
    public function testMoveOriginalDirectoryToInfrastructure() : void
    {
        $this->installer->install();

        $this->assertDirectoryExists($this->app->path(
            'Infrastructure/Core'
        ));

        $structure = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $this->app->path('Infrastructure/Core/Console'), FilesystemIterator::SKIP_DOTS
        ));

        collect($structure)
            ->map(function (SplFileInfo $spl) {
                return new OriFileInfo($this->app, $spl);
            })
            ->each(function (OriFileInfo $file) {
                $this->assertFileExists($file->getOriFileInfo());
            });

        $this->assertDirectoryDoesNotExist($this->app->path('Http'));
        $this->assertDirectoryDoesNotExist($this->app->path('Models'));
        $this->assertDirectoryDoesNotExist($this->app->path('Console'));
        $this->assertDirectoryDoesNotExist($this->app->path('Providers'));
        $this->assertDirectoryDoesNotExist($this->app->path('Exceptions'));
    }
}
