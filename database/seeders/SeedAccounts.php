<?php

namespace Database\Seeders;

/**
 * Shared demo login accounts for local / staging seed.
 */
final class SeedAccounts
{
    public static function managerEmail(): string
    {
        return (string) env('SEED_MANAGER_EMAIL', 'manager@zatochka.local');
    }

    public static function managerPassword(): string
    {
        return (string) env('SEED_MANAGER_PASSWORD', 'password123');
    }

    public static function masterEmail(): string
    {
        return (string) env('SEED_MASTER_EMAIL', 'master@zatochka.local');
    }

    public static function masterPassword(): string
    {
        return (string) env('SEED_MASTER_PASSWORD', 'password123');
    }

    public static function clientEmail(): string
    {
        return (string) env('SEED_CLIENT_EMAIL', 'client@zatochka.local');
    }

    public static function clientPassword(): string
    {
        return (string) env('SEED_CLIENT_PASSWORD', 'password123');
    }
}
