<?php

namespace Octopy\DDD\Support;

use FilesystemIterator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Octopy\DDD\Contracts\AutoRegister;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use UnexpectedValueException;

final class RegisterDomainServiceProvider
{
    /**
     * @var string
     */
    protected string $locations;

    /**
     * @var string
     */
    protected string $namespace;

    /**
     * @param  Application $app
     */
    public function __construct(protected readonly Application $app)
    {
        $this->locations = $app->path('Domain');
        $this->namespace = $app->getNamespace([
            //
        ]);
    }

    /**
     * @return void
     */
    public function register() : void
    {
        $this->discover()->each(function (ReflectionClass $class) {
            $this->app->register($class->getName());
        });
    }

    /**
     * @return Collection<ReflectionClass>
     */
    public function discover() : Collection
    {
        try {
            return collect(new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->locations, FilesystemIterator::SKIP_DOTS)
            ))
                ->filter(function (SplFileInfo $spl) {
                    return $spl->isFile() && class_exists($this->getClassName($spl));
                })
                ->map(function (SplFileInfo $spl) {
                    return new ReflectionClass($this->getClassName(
                        $spl
                    ));
                })
                ->filter(function (ReflectionClass $class) {
                    return $class->isSubclassOf(ServiceProvider::class) && $class->implementsInterface(AutoRegister::class);
                });
        } catch (UnexpectedValueException) {
            return collect([
                //
            ]);
        }
    }

    /**
     * @param  string $path
     * @return string
     */
    private function getClassName(string $path) : string
    {
        return $this->namespace . str_replace('/', '\\', substr(str_replace($this->locations, '', $path), 0, -4));
    }
}
