<?php

declare(strict_types=1);

class HolidayService
{
    /** أيام العمل في السعودية: الأحد–الخميس */
    public static function isWeekend(DateTimeInterface $date): bool
    {
        $dow = (int) $date->format('N');

        return $dow === 5 || $dow === 6;
    }

    public static function isHoliday(DateTimeInterface $date): bool
    {
        $dateStr = $date->format('Y-m-d');
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM holidays WHERE holiday_date = ? LIMIT 1');
        $stmt->execute([$dateStr]);

        return (bool) $stmt->fetch();
    }

    public static function isWorkday(DateTimeInterface $date): bool
    {
        return !self::isWeekend($date) && !self::isHoliday($date);
    }

    public static function workingDaysInMonth(int $year, int $month): int
    {
        $days = 0;
        $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $end = $start->modify('last day of this month');
        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            if (self::isWorkday($d)) {
                $days++;
            }
        }

        return $days;
    }

    public static function listAll(): array
    {
        $pdo = Database::getConnection();

        return $pdo->query('SELECT * FROM holidays ORDER BY holiday_date DESC')->fetchAll();
    }

    public static function create(string $date, string $name): void
    {
        $date = trim($date);
        $name = trim($name);
        if ($date === '' || $name === '') {
            throw new InvalidArgumentException('يرجى تعبئة التاريخ واسم العطلة.');
        }

        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO holidays (holiday_date, name) VALUES (?, ?)')->execute([$date, $name]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM holidays WHERE id = ?')->execute([$id]);
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            ['2025-09-23', 'اليوم الوطني السعودي'],
            ['2026-02-22', 'يوم التأسيس'],
            ['2026-09-23', 'اليوم الوطني السعودي'],
        ];

        $pdo = Database::getConnection();
        $check = $pdo->prepare('SELECT id FROM holidays WHERE holiday_date = ? LIMIT 1');
        $insert = $pdo->prepare('INSERT INTO holidays (holiday_date, name) VALUES (?, ?)');

        foreach ($defaults as [$date, $name]) {
            $check->execute([$date]);
            if (!$check->fetch()) {
                $insert->execute([$date, $name]);
            }
        }
    }
}
