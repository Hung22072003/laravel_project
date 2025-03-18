<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\Interaction\InteractionRepositoryImplementation;
use App\Repositories\Interaction\InteractionRepositoryInterface;
use App\Repositories\Post\PostRepositoryImplementation;
use App\Repositories\Post\PostRepositoryInterface;
use App\Repositories\SocialAccount\SocialAccountRepositoryImplementation;
use App\Repositories\SocialAccount\SocialAccountRepositoryInterface;
use App\Repositories\User\UserRepositoryImplementation;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register() {
        $this->app->bind(UserRepositoryInterface::class, UserRepositoryImplementation::class);
        $this->app->bind(SocialAccountRepositoryInterface::class, SocialAccountRepositoryImplementation::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepositoryImplementation::class);
        $this->app->bind(InteractionRepositoryInterface::class, InteractionRepositoryImplementation::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });
    }
}
