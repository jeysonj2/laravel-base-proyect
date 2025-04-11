<?php

namespace App\Providers;

use DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider.
 *
 * This service provider is responsible for registering application services
 * and bootstrapping functionality. It's where custom validation rules and
 * other application-wide services are defined.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is called by Laravel during the service provider registration process.
     * It's used to bind items into the service container.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     *
     * This method is called after all services are registered.
     * It's used to perform actions needed when the application is booting.
     *
     * In this project, it registers custom validation rules:
     * - case_insensitive_unique: For validating uniqueness regardless of letter case
     * - strong_password: For enforcing password complexity requirements
     */
    public function boot(): void
    {
        // Register a custom validation rule for case-insensitive uniqueness
        Validator::extend('case_insensitive_unique', function ($attribute, $value, $parameters, $validator) {
            $table = $parameters[0];
            $column = $parameters[1] ?? $attribute;
            $excludeId = $parameters[2] ?? null;

            $query = DB::table($table)->whereRaw("LOWER({$column}) = ?", [strtolower($value)]);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return ! $query->exists();
        });

        Validator::replacer('case_insensitive_unique', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, __('The :attribute has already been taken.'));
        });

        // Register a custom validation rule for strong passwords
        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            $minLength = env('PASSWORD_MIN_LENGTH', 10);
            $specialChars = preg_quote(env('PASSWORD_SPECIAL_CHARS', '!@#$%^&*()-_=+[]{}|;:,.<>?'), '/');

            $pattern = "/^(?=.*[A-Z])(?=.*[0-9])(?=.*[{$specialChars}]).{{$minLength},}$/";

            return preg_match($pattern, $value);
        });

        Validator::replacer('strong_password', function ($message, $attribute, $rule, $parameters) {
            return __('The :attribute must be at least :min characters long, contain at least one uppercase letter, ' .
                'one number, and one special character.', [
                    'attribute' => $attribute,
                    'min' => env('PASSWORD_MIN_LENGTH', 10),
                ]);
        });

        // Read the APP_URL from the environment to force HTTPS
        // This is useful for ensuring that all generated URLs use HTTPS
        // when the application is running in a secure environment.
        $appUrl = env('APP_URL');
        // Check if it is https
        if ($appUrl && str_starts_with($appUrl, 'https://')) {
            // Set the URL scheme to https
            URL::forceScheme('https');
        }
    }
}
