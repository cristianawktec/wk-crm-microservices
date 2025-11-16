<?php

namespace App\Providers;

use App\Domain\Customer\CustomerRepositoryInterface;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Infrastructure\Repositories\CustomerRepositoryEloquent;
use App\Infrastructure\Repositories\LeadRepositoryEloquent;
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

            // Bind LeadRepository
            $this->app->bind(
                LeadRepositoryInterface::class,
                LeadRepositoryEloquent::class
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
