<?php

declare(strict_types=1);

class AttendanceService
{
    public static function todayStatus(int $userId, string $timezone): array
    {
        $utcNow = TimezoneHelper::utcNow();
        $localDate = TimezoneHelper::localWorkDate($utcNow, $timezone);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT * FROM attendance_records WHERE user_id = ? AND local_work_date = ? ORDER BY signed_at_utc'
        );
        $stmt->execute([$userId, $localDate]);
        $records = $stmt->fetchAll();

        $checkIn = null;
        $checkOut = null;
        foreach ($records as $r) {
            if ($r['type'] === 'check_in') {
                $checkIn = $r;
            }
            if ($r['type'] === 'check_out') {
                $checkOut = $r;
            }
        }

        return [
            'local_date' => $localDate,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ];
    }

    public static function sign(int $userId, string $type, string $signatureData, string $timezone, string $ip): void
    {
        if (!in_array($type, ['check_in', 'check_out'], true)) {
            throw new InvalidArgumentException('نوع التوقيع غير صالح.');
        }
        if (strlen($signatureData) < 100) {
            throw new InvalidArgumentException('يرجى التوقيع الإلكتروني قبل الإرسال.');
        }

        $utcNow = TimezoneHelper::utcNow();
        $localDate = TimezoneHelper::localWorkDate($utcNow, $timezone);

        $pdo = Database::getConnection();
        $existing = $pdo->prepare(
            'SELECT id FROM attendance_records WHERE user_id = ? AND local_work_date = ? AND type = ?'
        );
        $existing->execute([$userId, $localDate, $type]);
        if ($existing->fetch()) {
            throw new RuntimeException($type === 'check_in' ? 'تم تسجيل الحضور اليوم مسبقاً.' : 'تم تسجيل الانصراف اليوم مسبقاً.');
        }

        if ($type === 'check_out') {
            $in = $pdo->prepare(
                'SELECT id FROM attendance_records WHERE user_id = ? AND local_work_date = ? AND type = ?'
            );
            $in->execute([$userId, $localDate, 'check_in']);
            if (!$in->fetch()) {
                throw new RuntimeException('يجب تسجيل الحضور قبل الانصراف.');
            }
        }

        $stmt = $pdo->prepare(
            'INSERT INTO attendance_records (user_id, type, signed_at_utc, local_work_date, timezone, signature_data, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $type,
            $utcNow->format('Y-m-d H:i:s'),
            $localDate,
            $timezone,
            $signatureData,
            $ip,
        ]);
    }

    public static function recent(int $userId, int $days = 7): array
    {
        $since = (new DateTimeImmutable())->modify("-{$days} days")->format('Y-m-d');

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT * FROM attendance_records WHERE user_id = ?
             AND local_work_date >= ?
             ORDER BY local_work_date DESC, signed_at_utc DESC'
        );
        $stmt->execute([$userId, $since]);

        return $stmt->fetchAll();
    }

    public static function teamAttendance(int $managerId, string $date): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.name, u.timezone,
                    MAX(CASE WHEN a.type = "check_in" THEN a.signed_at_utc END) AS check_in_utc,
                    MAX(CASE WHEN a.type = "check_out" THEN a.signed_at_utc END) AS check_out_utc
             FROM users u
             LEFT JOIN attendance_records a ON a.user_id = u.id AND a.local_work_date = ?
             WHERE u.manager_id = ? AND u.role = "employee" AND u.is_active = 1
             GROUP BY u.id, u.name, u.timezone
             ORDER BY u.name'
        );
        $stmt->execute([$date, $managerId]);
        return $stmt->fetchAll();
    }
}
