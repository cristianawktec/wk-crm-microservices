<?php

namespace App\Providers;

use App\Domain\Customer\CustomerRepositoryInterface;
use App\Infrastructure\Repositories\CustomerRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para binding de Repositories
 * Implementa Dependency Inversion Principle (SOLID)
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind CustomerRepository
        $this->app->bind(
            CustomerRepositoryInterface::class,
            CustomerRepositoryEloquent::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
