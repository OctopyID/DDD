<?php

namespace Octopy\DDD\Installer\FileInfo;

use Illuminate\Foundation\Application;
use SplFileInfo;

class OriFileInfo
{
    /**
     * @var SplFileInfo
     */
    protected readonly SplFileInfo $file;

    /**
     * @param  Application        $app
     * @param  SplFileInfo|string $file
     */
    public function __construct(protected readonly Application $app, SplFileInfo|string $file)
    {
        $this->file = is_string($file) ? new SplFileInfo($file) : $file;
    }

    /**
     * @return string
     */
    public function getOriClassName() : string
    {
        return $this->pathToClassName($this->getOriFileInfo());
    }

    /**
     * @return string
     */
    public function getNewClassName() : string
    {
        return $this->pathToClassName($this->getNewFileInfo());
    }

    /**
     * @return string
     */
    public function getOriNameSpace() : string
    {
        return str($this->getOriClassName())->substr(0, -(
            strlen(class_basename($this->getOriClassName())) + 1
        ));
    }

    /**
     * @return string
     */
    public function getNewNameSpace() : string
    {
        return str($this->getNewClassName())->substr(0, -(
            strlen(class_basename($this->getNewClassName())) + 1
        ));
    }

    /**
     * @return SplFileInfo
     */
    public function getOriFileInfo() : SplFileInfo
    {
        return new SplFileInfo(
            preg_replace('#/+#', '/', $this->file->getPathname())
        );
    }

    /**
     * @return SplFileInfo
     */
    public function getNewFileInfo() : SplFileInfo
    {
        return new SplFileInfo(
            str($this->file->getPathname())->substrReplace('/Infrastructure/Core', strlen($this->app->path()), 0)->replaceMatches('#/+#', '/')
        );
    }

    /**
     * @param  SplFileInfo $spl
     * @return string
     */
    protected function pathToClassName(SplFileInfo $spl) : string
    {
        return str($spl->getPathname())->replace($this->app->path(), '')->replace('.php', '')->trim('/')->replace('/', '\\')->prepend($this->app->getNamespace([

        ]));
    }
}
