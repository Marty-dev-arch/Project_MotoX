<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatePeriods
{
    public const PERIOD_ALL = 'all';
    public const PERIOD_DAILY = 'daily';
    public const PERIOD_WEEKLY = 'weekly';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_YEARLY = 'yearly';

    public static function normalize(?string $period): string
    {
        $normalized = Str::of((string) $period)->trim()->lower()->toString();

        return match ($normalized) {
            'general', 'all-time', 'all_time', 'all' => self::PERIOD_ALL,
            'today', 'day', 'daily' => self::PERIOD_DAILY,
            'week', 'weekly' => self::PERIOD_WEEKLY,
            'month', 'monthly' => self::PERIOD_MONTHLY,
            'year', 'yearly' => self::PERIOD_YEARLY,
            default => self::PERIOD_ALL,
        };
    }

    public static function bounds(string $period, string $timezone = 'Asia/Manila'): ?array
    {
        $now = now($timezone);

        $bounds = match (self::normalize($period)) {
            self::PERIOD_DAILY => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            self::PERIOD_WEEKLY => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            self::PERIOD_MONTHLY => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            self::PERIOD_YEARLY => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => null,
        };

        return $bounds
            ? [$bounds[0]->copy()->utc(), $bounds[1]->copy()->utc()]
            : null;
    }

    public static function label(string $period): string
    {
        return match (self::normalize($period)) {
            self::PERIOD_DAILY => 'Daily',
            self::PERIOD_WEEKLY => 'Weekly',
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_YEARLY => 'Yearly',
            default => 'General',
        };
    }

    public static function filenameToken(string $period): string
    {
        return Str::of(self::label($period))->lower()->replace(' ', '-')->toString();
    }
}
