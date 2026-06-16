<?php

declare(strict_types=1);

class AccountService
{
    private const AVATAR_MAX_BYTES = 2_097_152;
    private const AVATAR_DIR = '/uploads/avatars';

    public static function changePassword(int $userId, string $current, string $new, string $confirm): void
    {
        if (strlen($new) < UserService::MIN_PASSWORD_LENGTH) {
            throw new InvalidArgumentException('كلمة المرور الجديدة يجب أن تكون ' . UserService::MIN_PASSWORD_LENGTH . ' أحرف على الأقل.');
        }
        if ($new !== $confirm) {
            throw new InvalidArgumentException('تأكيد كلمة المرور غير متطابق.');
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row['password_hash'])) {
            throw new RuntimeException('كلمة المرور الحالية غير صحيحة.');
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
    }

    public static function updateEmail(int $userId, string $email): void
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            throw new InvalidArgumentException('يرجى إدخال البريد الإلكتروني.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('البريد الإلكتروني غير صالح.');
        }

        $pdo = Database::getConnection();
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
        $exists->execute([$email, $userId]);
        if ($exists->fetch()) {
            throw new RuntimeException('البريد الإلكتروني مستخدم مسبقاً.');
        }

        $pdo->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $userId]);
    }

    public static function uploadAvatar(int $userId, array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('يرجى اختيار صورة.');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('فشل رفع الملف.');
        }
        if (($file['size'] ?? 0) > self::AVATAR_MAX_BYTES) {
            throw new InvalidArgumentException('حجم الصورة يجب ألا يتجاوز 2 ميغابايت.');
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('ملف غير صالح.');
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new InvalidArgumentException('الملف المرفوع ليس صورة صالحة.');
        }

        $mime = $info['mime'] ?? '';
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => throw new InvalidArgumentException('نوع الصورة غير مدعوم. استخدم JPG أو PNG أو WebP.'),
        };

        $publicRoot = dirname(__DIR__) . '/public';
        $dir = $publicRoot . self::AVATAR_DIR;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('تعذّر إنشاء مجلد الصور.');
        }

        $filename = $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $relativePath = ltrim(self::AVATAR_DIR, '/') . '/' . $filename;
        $absolutePath = $publicRoot . '/' . $relativePath;

        if (!move_uploaded_file($tmp, $absolutePath)) {
            throw new RuntimeException('تعذّر حفظ الصورة.');
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT avatar_path FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $old = $stmt->fetchColumn();

        $pdo->prepare('UPDATE users SET avatar_path = ? WHERE id = ?')->execute([$relativePath, $userId]);

        if (is_string($old) && $old !== '' && $old !== $relativePath) {
            $oldFile = $publicRoot . '/' . ltrim($old, '/');
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        return $relativePath;
    }
}
