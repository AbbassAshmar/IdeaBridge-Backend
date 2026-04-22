<?php

namespace App\Providers;

use App\Modules\Auth\Repositories\AuthRepositoryInterface;
use App\Modules\Auth\Repositories\EloquentAuthRepository;
use App\Modules\Categories\Repositories\CategoryRepositoryInterface;
use App\Modules\Categories\Repositories\EloquentCategoryRepository;
use App\Modules\Ideas\Repositories\EloquentIdeaRepository;
use App\Modules\Ideas\Repositories\IdeaRepositoryInterface;
use App\Modules\Users\Repositories\EloquentUserRepository;
use App\Modules\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, EloquentAuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(IdeaRepositoryInterface::class, EloquentIdeaRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
