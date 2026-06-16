<?php

declare(strict_types=1);

class TimezoneHelper
{
    public static function utcNow(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public static function toLocal(string $utcDatetime, string $timezone): DateTimeImmutable
    {
        $dt = new DateTimeImmutable($utcDatetime, new DateTimeZone('UTC'));
        return $dt->setTimezone(new DateTimeZone($timezone));
    }

    public static function localWorkDate(DateTimeImmutable $utcNow, string $timezone): string
    {
        return $utcNow->setTimezone(new DateTimeZone($timezone))->format('Y-m-d');
    }

    public static function formatArabic(string $utcDatetime, string $timezone): string
    {
        $local = self::toLocal($utcDatetime, $timezone);
        return $local->format('Y-m-d H:i');
    }

    public static function localToUtc(string $localDatetime, string $timezone): DateTimeImmutable
    {
        $dt = new DateTimeImmutable($localDatetime, new DateTimeZone($timezone));
        return $dt->setTimezone(new DateTimeZone('UTC'));
    }

    public static function commonTimezones(): array
    {
        return [
            'Asia/Riyadh' => 'الرياض (GMT+3)',
            'Asia/Damascus' => 'دمشق (GMT+3)',
            'Asia/Gaza' => 'فلسطين (GMT+2/+3)',
            'Africa/Cairo' => 'القاهرة (GMT+2)',
            'Asia/Dubai' => 'دبي (GMT+4)',
            'Asia/Kuwait' => 'الكويت (GMT+3)',
            'Asia/Amman' => 'عمّان (GMT+3)',
            'Asia/Baghdad' => 'بغداد (GMT+3)',
            'UTC' => 'UTC',
        ];
    }

    public static function isValid(string $timezone): bool
    {
        return array_key_exists($timezone, self::commonTimezones());
    }
}
