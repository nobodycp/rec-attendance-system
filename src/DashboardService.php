<?php

declare(strict_types=1);

class DashboardService
{
    public static function adminStats(string $today): array
    {
        $pdo = Database::getConnection();

        $totalEmployees = (int) $pdo->query(
            'SELECT COUNT(*) FROM users WHERE role = "employee" AND is_active = 1'
        )->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(DISTINCT u.id) FROM users u
             INNER JOIN attendance_records a ON a.user_id = u.id AND a.local_work_date = ? AND a.type = "check_in"
             WHERE u.role = "employee" AND u.is_active = 1'
        );
        $stmt->execute([$today]);
        $presentToday = (int) $stmt->fetchColumn();

        $pendingTasks = (int) $pdo->query(
            'SELECT COUNT(*) FROM daily_tasks WHERE status = "pending"'
        )->fetchColumn();

        $awaitingEvaluation = (int) $pdo->query(
            'SELECT COUNT(*) FROM daily_tasks WHERE status = "completed"'
        )->fetchColumn();

        $isWorkday = HolidayService::isWorkday(new DateTimeImmutable($today));

        return [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'absent_today' => $isWorkday ? max(0, $totalEmployees - $presentToday) : 0,
            'pending_tasks' => $pendingTasks,
            'awaiting_evaluation' => $awaitingEvaluation,
            'is_workday' => $isWorkday,
        ];
    }
}
