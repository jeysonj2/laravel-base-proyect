<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register a custom validation rule for case-insensitive uniqueness
        Validator::extend('case_insensitive_unique', function ($attribute, $value, $parameters, $validator) {
            $table = $parameters[0];
            $column = $parameters[1] ?? $attribute;
            $excludeId = $parameters[2] ?? null;

            $query = \DB::table($table)->whereRaw("LOWER($column) = ?", [strtolower($value)]);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return !$query->exists();
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
            return __('The :attribute must be at least :min characters long, contain at least one uppercase letter, one number, and one special character.', [
                'attribute' => $attribute,
                'min' => env('PASSWORD_MIN_LENGTH', 10),
            ]);
        });
    }
}
