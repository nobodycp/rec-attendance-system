<?php

declare(strict_types=1);

class TaskService
{
    public static function create(int $employeeId, int $assignedBy, string $title, ?string $description, string $taskDate): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO daily_tasks (employee_id, assigned_by, title, description, task_date) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$employeeId, $assignedBy, $title, $description, $taskDate]);
        return (int) $pdo->lastInsertId();
    }

    public static function getById(int $taskId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT t.*, e.name AS employee_name, m.name AS manager_name
             FROM daily_tasks t
             JOIN users e ON e.id = t.employee_id
             JOIN users m ON m.id = t.assigned_by
             WHERE t.id = ?'
        );
        $stmt->execute([$taskId]);
        return $stmt->fetch() ?: null;
    }

    public static function forEmployee(int $employeeId, ?string $from = null, ?string $to = null): array
    {
        $pdo = Database::getConnection();
        $sql = 'SELECT t.*, tc.completed_at_utc, tc.notes AS completion_notes,
                       te.score, te.notes AS evaluation_notes
                FROM daily_tasks t
                LEFT JOIN task_completions tc ON tc.task_id = t.id
                LEFT JOIN task_evaluations te ON te.task_id = t.id
                WHERE t.employee_id = ?';
        $params = [$employeeId];
        if ($from) {
            $sql .= ' AND t.task_date >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND t.task_date <= ?';
            $params[] = $to;
        }
        $sql .= ' ORDER BY t.task_date DESC, t.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function forManager(int $managerId, ?string $from = null, ?string $to = null): array
    {
        $pdo = Database::getConnection();
        $sql = 'SELECT t.*, e.name AS employee_name, tc.completed_at_utc, te.score
                FROM daily_tasks t
                JOIN users e ON e.id = t.employee_id
                LEFT JOIN task_completions tc ON tc.task_id = t.id
                LEFT JOIN task_evaluations te ON te.task_id = t.id
                WHERE e.manager_id = ?';
        $params = [$managerId];
        if ($from) {
            $sql .= ' AND t.task_date >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND t.task_date <= ?';
            $params[] = $to;
        }
        $sql .= ' ORDER BY t.task_date DESC, t.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function complete(int $taskId, int $completedBy, string $localDatetime, string $timezone, ?string $notes): void
    {
        $task = self::getById($taskId);
        if (!$task) {
            throw new RuntimeException('المهمة غير موجودة.');
        }
        if ($task['status'] !== 'pending') {
            throw new RuntimeException('المهمة مكتملة أو مُقيَّمة مسبقاً.');
        }

        $utc = TimezoneHelper::localToUtc($localDatetime, $timezone);
        $local = TimezoneHelper::toLocal($utc->format('Y-m-d H:i:s'), $timezone);

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO task_completions (task_id, completed_by, completed_at_utc, completed_at_local, notes)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $taskId,
            $completedBy,
            $utc->format('Y-m-d H:i:s'),
            $local->format('Y-m-d H:i:s'),
            $notes,
        ]);
        $pdo->prepare('UPDATE daily_tasks SET status = ? WHERE id = ?')->execute(['completed', $taskId]);
        $pdo->commit();
    }

    public static function evaluate(int $taskId, int $evaluatedBy, int $score, ?string $notes): void
    {
        if ($score < 1 || $score > 10) {
            throw new InvalidArgumentException('الدرجة يجب أن تكون بين 1 و 10.');
        }

        $task = self::getById($taskId);
        if (!$task) {
            throw new RuntimeException('المهمة غير موجودة.');
        }
        if ($task['status'] !== 'completed') {
            throw new RuntimeException('يجب إتمام المهمة قبل التقييم.');
        }

        $utcNow = TimezoneHelper::utcNow();
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO task_evaluations (task_id, evaluated_by, score, notes, evaluated_at_utc)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$taskId, $evaluatedBy, $score, $notes, $utcNow->format('Y-m-d H:i:s')]);
        $pdo->prepare('UPDATE daily_tasks SET status = ? WHERE id = ?')->execute(['evaluated', $taskId]);
        $pdo->commit();
    }

    public static function teamEmployees(int $managerId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT id, name, email, timezone FROM users WHERE manager_id = ? AND role = "employee" AND is_active = 1 ORDER BY name'
        );
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }

    public static function canAccess(int $taskId, int $userId, string $role): bool
    {
        $task = self::getById($taskId);
        if (!$task) {
            return false;
        }
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'employee' && (int) $task['employee_id'] === $userId) {
            return true;
        }
        if (in_array($role, ['manager', 'dept_manager', 'admin'], true)) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT manager_id FROM users WHERE id = ?');
            $stmt->execute([$task['employee_id']]);
            $emp = $stmt->fetch();
            return $emp && (int) $emp['manager_id'] === $userId;
        }
        return false;
    }
}
