<?php

declare(strict_types=1);

class ExportService
{
    public static function reportCsv(array $report): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $lines = [];
        $lines[] = 'الموظف,' . self::csv($report['user']['name']);
        $lines[] = 'الشهر,' . self::csv(($months[$report['month']] ?? '') . ' ' . $report['year']);
        $lines[] = 'درجة الحضور,' . $report['attendance']['attendance_score'] . '%';
        $lines[] = 'درجة الأداء,' . ($report['performance']['performance_score'] ?? '—');
        $lines[] = '';
        $lines[] = 'التاريخ,يوم عمل,حضور,انصراف,مكتمل';

        foreach ($report['attendance']['daily'] as $day) {
            $lines[] = implode(',', [
                $day['date'],
                $day['is_workday'] ? 'نعم' : 'لا',
                $day['check_in'] ? 'نعم' : 'لا',
                $day['check_out'] ? 'نعم' : 'لا',
                $day['complete'] ? 'نعم' : 'لا',
            ]);
        }

        $lines[] = '';
        $lines[] = 'تاريخ المهمة,المهمة,الحالة,الدرجة';
        foreach ($report['performance']['tasks'] as $task) {
            $lines[] = implode(',', [
                $task['task_date'],
                self::csv($task['title']),
                self::csv(statusLabel($task['status'])),
                $task['score'] ?? '—',
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\n", $lines);
    }

    private static function csv(string $value): string
    {
        $value = str_replace('"', '""', $value);

        return '"' . $value . '"';
    }
}
