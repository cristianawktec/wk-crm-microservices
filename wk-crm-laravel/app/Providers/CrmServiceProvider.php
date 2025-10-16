<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Customer\Repositories\EloquentCustomerRepository;
use App\Application\Customer\UseCases\CreateCustomerUseCase;
use App\Application\Customer\UseCases\GetAllCustomersUseCase;

class CrmServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interface to Implementation
        $this->app->bind(
            CustomerRepositoryInterface::class,
            EloquentCustomerRepository::class
        );

        // Register Use Cases
        $this->app->singleton(CreateCustomerUseCase::class, function ($app) {
            return new CreateCustomerUseCase(
                $app->make(CustomerRepositoryInterface::class)
            );
        });

        $this->app->singleton(GetAllCustomersUseCase::class, function ($app) {
            return new GetAllCustomersUseCase(
                $app->make(CustomerRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
