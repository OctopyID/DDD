<?php

namespace Octopy\DDD\Installer;

use FilesystemIterator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Octopy\DDD\Installer\FileInfo\NewFileInfo;
use Octopy\DDD\Installer\FileInfo\OriFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class Installer
{
    /**
     * @var Collection
     */
    protected Collection $paths;

    /**
     * @param  Application $app
     */
    public function __construct(protected readonly Application $app)
    {
        $this->paths = collect([
            'Domain',
            'Support',
            'Application',
            'Infrastructure',
            'Infrastructure/Core',
        ])
            ->map(function (string $path) : string {
                return $this->app->path($path);
            });
    }

    /**
     * @return Collection<OriFileInfo|NewFileInfo>
     */
    public function getItems() : Collection
    {
        $structure = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $this->app->path('/'), FilesystemIterator::SKIP_DOTS
        ));

        return collect($structure)->map(function (SplFileInfo $spl) {
            return new OriFileInfo($this->app, $spl);
        });
    }

    /**
     * @return $this
     */
    public function install() : self
    {
        $items = $this->getItems();

        $this->createMandatoryStructure();

        $items->each(function (OriFileInfo $item) {
            $this
                ->moveAndReplaceNameSpace($item)
                ->replaceClassNameOnBootstrapFile($item)
                ->replaceClassNameOnConfigDirectories($item);
        });

        foreach (glob($this->app->path('*')) as $path) {
            if (! in_array($path, $this->paths->toArray())) {
                $this->app->make('files')->deleteDirectory($path);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function createMandatoryStructure() : self
    {
        $file = $this->app->make('files');

        $this->paths->each(function (string $path) use ($file) {
            $file->ensureDirectoryExists($path);
            $file->put(str($path)->append('/.gitkeep'), '.gitkeep');
        });

        return $this;
    }

    /**
     * @return $this
     */
    public function restoreOriginalStructure() : self
    {
        if (! is_dir($this->app->path('Infrastructure/Core'))) {
            return $this;
        }

        $structure = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $this->app->path('Infrastructure/Core'), FilesystemIterator::SKIP_DOTS
        ));

        collect($structure)
            ->reject(function (SplFileInfo $spl) {
                return $spl->getFilename() === '.gitkeep';
            })
            ->map(function (SplFileInfo $spl) {
                return new NewFileInfo($this->app, $spl);
            })
            ->each(function (NewFileInfo $item) {
                $this
                    ->moveAndReplaceNameSpace($item)
                    ->replaceClassNameOnBootstrapFile($item)
                    ->replaceClassNameOnConfigDirectories($item);
            });

        $this->paths->each(function (string $path) {
            $this->app->make('files')->deleteDirectory($path);
        });

        return $this;
    }

    /**
     * @param  OriFileInfo|NewFileInfo $item
     * @return Installer
     */
    public function moveAndReplaceNameSpace(OriFileInfo|NewFileInfo $item) : self
    {
        $file = $this->app->make('files');

        $file->ensureDirectoryExists(
            $item->getNewFileInfo()->getPath()
        );

        $file->move(
            $item->getOriFileInfo(), $item->getNewFileInfo()
        );

        $file->put($item->getNewFileInfo(), str_replace($item->getOriNameSpace(), $item->getNewNameSpace(), file_get_contents(
            $item->getNewFileInfo()->getPathname()
        )));

        return $this;
    }

    /**
     * @param  NewFileInfo|OriFileInfo $item
     * @return Installer
     */
    public function replaceClassNameOnBootstrapFile(NewFileInfo|OriFileInfo $item) : self
    {
        $bootstrap = $this->app->bootstrapPath('app.php');

        $this->app->make('files')->put($bootstrap, str_replace($item->getOriClassName(), $item->getNewClassName(), file_get_contents(
            $bootstrap
        )));

        return $this;
    }

    /**
     * @param  NewFileInfo|OriFileInfo $item
     * @return void
     */
    public function replaceClassNameOnConfigDirectories(NewFileInfo|OriFileInfo $item) : void
    {
        $configs = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $this->app->configPath(), FilesystemIterator::SKIP_DOTS
        ));

        /**
         * @var SplFileInfo $config
         */
        foreach ($configs as $config) {
            $this->app->make('files')->put($config, str_replace($item->getOriClassName(), $item->getNewClassName(), file_get_contents(
                $config
            )));
        }
    }
}
