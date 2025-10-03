<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
         $this->app->singleton(LoginViewResponse::class, function () {
        return new class implements LoginViewResponse {
            public function toResponse($request)
            {
                return new JsonResponse(['message' => 'Login view not available'], 404);
            }
        };
    });

    $this->app->singleton(RegisterViewResponse::class, function () {
        return new class implements RegisterViewResponse {
            public function toResponse($request)
            {
                return new JsonResponse(['message' => 'Register view not available'], 404);
            }
        };
    });

    $this->app->singleton(RequestPasswordResetLinkViewResponse::class, function () {
        return new class implements RequestPasswordResetLinkViewResponse {
            public function toResponse($request)
            {
                return new JsonResponse(['message' => 'Password reset view not available'], 404);
            }
        };
    });

    $this->app->singleton(ResetPasswordViewResponse::class, function () {
        return new class implements ResetPasswordViewResponse {
            public function toResponse($request)
            {
                return new JsonResponse(['message' => 'Reset password view not available'], 404);
            }
        };
    });

    $this->app->singleton(VerifyEmailViewResponse::class, function () {
        return new class implements VerifyEmailViewResponse {
            public function toResponse($request)
            {
                return new JsonResponse(['message' => 'Email verification view not available'], 404);
            }
        };
    });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         Fortify::ignoreRoutes();
        // Fortify::createUsersUsing(CreateNewUser::class);
        // Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        // Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        // Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        // Fortify::ignoreRoutes();
    }
}
