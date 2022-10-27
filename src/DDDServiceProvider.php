<?php

namespace Octopy\DDD;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Octopy\DDD\Console\DomainDrivenDesignCommand;
use Octopy\DDD\Support\RegisterDomainServiceProvider;

final class DDDServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function register() : void
    {
        $this->app->make(RegisterDomainServiceProvider::class)->register();
    }

    /**
     * @return void
     */
    public function boot() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DomainDrivenDesignCommand::class,
            ]);
        }
    }
}
