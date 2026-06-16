# دليل نشر نظام REC على Hostinger

> **موصى به للإنتاج:** استخدم Docker على [Coolify](COOLIFY-DEPLOY-AR.md).  
> هذا الدليل للاستضافة PHP التقليدية (Hostinger) بدون Docker.

## الرابط الذي ترسله للموظفين والمشرفين

بعد النشر:

```
https://YOUR-DOMAIN.com/login
```

استبدل `YOUR-DOMAIN.com` بنطاقك الفعلي (مثلاً `rec.org` أو `attendance.rec.org`).

---

## الخطوة 1 — إنشاء قاعدة بيانات MySQL

1. ادخل **hPanel** → **Databases** → **MySQL Databases**
2. أنشئ قاعدة بيانات جديدة (مثلاً `u123456789_rec`)
3. أنشئ مستخدم MySQL وربطه بالقاعدة — **احفظ**:
   - Database name
   - Username
   - Password
   - Host (غالباً `localhost`)

---

## الخطوة 2 — استيراد الجداول

1. hPanel → **phpMyAdmin**
2. اختر قاعدة البيانات
3. **Import** → ارفع الملف: `database/schema.sql`
4. اضغط **Go**

---

## الخطوة 3 — رفع الملفات

### الهيكل الموصى به على Hostinger

```
domains/yourdomain.com/
├── config/          ← خارج public_html (محمي)
├── src/
├── views/
├── database/
└── public_html/     ← محتويات مجلد public/ فقط
    ├── index.php
    ├── router.php
    ├── setup.php
    ├── .htaccess
    └── assets/
```

**من File Manager أو FTP:**

| من جهازك | إلى الاستضافة |
|----------|---------------|
| `attendance-system/public/*` | `public_html/` |
| `attendance-system/config/` | `config/` (بجانب public_html) |
| `attendance-system/src/` | `src/` |
| `attendance-system/views/` | `views/` |
| `attendance-system/database/` | `database/` (للنسخ الاحتياطي فقط) |

> إذا كان `public_html` داخل `domains/yourdomain.com/`، ضع `config` و `src` و `views` في **نفس المستوى** وليس داخل `public_html`.

---

## الخطوة 4 — إعداد config.php

1. انسخ `config/config.example.php` → `config/config.php`
2. عدّل القيم:

```php
'db' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'name' => 'u123456789_rec',      // اسم قاعدتك
    'user' => 'u123456789_user',      // مستخدم MySQL
    'pass' => 'YOUR_DB_PASSWORD',
    'charset' => 'utf8mb4',
],
'app' => [
    'name' => 'جمعية مركز الإرشاد التربوي REC',
    'url' => 'https://yourdomain.com',  // نطاقك الكامل
    'debug' => false,
],
```

---

## الخطوة 5 — إنشاء حساب مسؤول النظام

افتح في المتصفح **مرة واحدة**:

```
https://yourdomain.com/setup.php
```

- أدخل بريد وكلمة مرور مسؤول النظام
- **احذف `setup.php` فوراً** من `public_html`

---

## الخطوة 6 — تفعيل HTTPS

1. hPanel → **SSL** → فعّل شهادة مجانية
2. hPanel → **Advanced** → **Force HTTPS**
3. في `public_html/.htaccess` أزل `#` من سطور إعادة التوجيه إلى HTTPS

---

## الخطوة 7 — إضافة المستخدمين

1. سجّل دخول كمسؤول نظام
2. **الموظفون** → أضف مشرفين وموظفين
3. أرسل لكل شخص:
   - الرابط: `https://yourdomain.com/login`
   - البريد وكلمة المرور

---

## إعداد PHP

hPanel → **Advanced** → **PHP Configuration** → اختر **PHP 8.1** أو أحدث.

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| 500 Internal Error | تحقق من `config.php` وبيانات MySQL |
| 404 على `/login` | تأكد من وجود `.htaccess` في `public_html` |
| صفحة بيضاء | عيّن `'debug' => true` مؤقتاً واقرأ الخطأ |
| لا يعمل التوقيع | تأكد أن JavaScript مفعّل في المتصفح |

---

## نشر على مجلد فرعي (اختياري)

إذا رفعت داخل `public_html/attendance/`:

```php
'url' => 'https://yourdomain.com/attendance',
'base_path' => '/attendance',
```

الرابط للمستخدمين: `https://yourdomain.com/attendance/login`

---

## ملخص سريع

1. MySQL + استيراد `schema.sql`
2. رفع الملفات بالهيكل أعلاه
3. `config.php` ببيانات الاستضافة
4. `setup.php` → إنشاء المسؤول → حذف `setup.php`
5. أرسل `https://yourdomain.com/login` للجميع
