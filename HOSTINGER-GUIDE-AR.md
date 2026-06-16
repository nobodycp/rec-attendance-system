# دليل النشر الكامل — جمعية REC على Hostinger

> **موصى به للإنتاج:** [COOLIFY-DEPLOY-AR.md](COOLIFY-DEPLOY-AR.md) — Docker على Coolify.

> **كيف نعمل معاً:** نفّذ كل مرحلة، ثم أخبرني «انتهيت من المرحلة X» وسأساعدك في التالية.

---

## ما تحتاجه قبل البدء

- بريد إلكتروني فعّال (Gmail مثلاً)
- بطاقة بنكية أو PayPal للدفع
- **30–60 دقيقة** للمرحلة الأولى
- مجلد المشروع على جهازك: `attendance-system/`

**الخطة المناسبة:** **Premium** أو **Business** (Shared Hosting) — تدعم PHP + MySQL.

---

# المرحلة 1 — إنشاء حساب Hostinger

### 1.1 افتح الموقع
👉 https://www.hostinger.com

- اختر **Web Hosting** (استضافة مواقع)
- الخطة **Premium** كافية للبداية (موقع واحد + قاعدة بيانات + SSL مجاني)

### 1.2 اختر مدة الاشتراك
- **12 شهر** أو أطول = سعر أقل شهرياً
- أكمل إلى صفحة الدفع

### 1.3 سجّل حساباً
- الاسم الكامل
- بريد إلكتروني **تحتفظ به** (ستصلك فواتير Hostinger عليه)
- كلمة مرور قوية **احفظها**

### 1.4 ادفع
- Visa / Mastercard / PayPal
- انتظر رسالة «تم الدفع بنجاح»

### 1.5 اختر نطاقاً (Domain)
عند الدفع يُطلب منك:

| الخيار | مثال | ملاحظة |
|--------|------|--------|
| **نطاق جديد مجاني** | `rec-sy.org` أو `rec-center.org` | يُفضّل اسم قصير وسهل |
| **نطاق تملكه مسبقاً** | اربطه لاحقاً من hPanel | |

> **مهم:** اكتب اسم النطاق الذي اخترته — سنستخدمه في `config.php`.

### 1.6 ادخل لوحة التحكم hPanel
👉 https://hpanel.hostinger.com

**✅ عند الانتهاء أخبرني:**
1. هل تم إنشاء الحساب؟
2. ما اسم النطاق؟ (مثلاً `myrec.org`)
3. هل دخلت hPanel بنجاح؟

---

# المرحلة 2 — قاعدة بيانات MySQL

*(ننفّذها بعد تأكيدك للمرحلة 1)*

1. hPanel → **Websites** → اختر موقعك
2. **Databases** → **MySQL Databases**
3. **Create database**:
   - Database name: `rec_attendance` (أو الاسم الذي يقترحه Hostinger)
   - Username + Password → **انسخها في ملف نصي**
4. Host غالباً: `localhost`

**✅ أرسل لي (بدون كلمة المرور في المحادقة العامة — أو قل «حفظتها»):**
- Database name
- Username  
- Host

---

# المرحلة 3 — استيراد الجداول (phpMyAdmin)

1. hPanel → **Databases** → **phpMyAdmin**
2. من القائمة اليسرى → اختر قاعدة بياناتك
3. تبويب **Import**
4. **Choose file** → اختر من جهازك:
   ```
   attendance-system/database/schema.sql
   ```
5. **Go** / **تنفيذ**
6. يجب أن تظهر 5 جداول: `users`, `attendance_records`, `daily_tasks`, ...

**✅ أخبرني:** «تم الاستيراد» أو أرسل لقطة إن ظهر خطأ.

---

# المرحلة 4 — رفع الملفات

### الهيكل على Hostinger

```
/home/u123456789/domains/YOUR-DOMAIN.com/
├── config/
├── src/
├── views/
├── database/
└── public_html/     ← محتويات مجلد public فقط
```

### 4.1 من hPanel → File Manager

**ارفع هذه المجلدات** (بجانب `public_html` وليس داخله):
- `config/` ← من مشروعك
- `src/`
- `views/`
- `database/` (اختياري للنسخ الاحتياطي)

**داخل `public_html/`** ارفع محتويات `public/`:
- `index.php`
- `router.php`
- `setup.php`
- `.htaccess`
- مجلد `assets/`

### 4.2 إنشاء config.php

1. في `config/` انسخ `config.example.php` → سمّه `config.php`
2. عدّل (سأساعدك بالقيم الدقيقة):

```php
'db' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'name' => 'اسم_قاعدتك',
    'user' => 'مستخدم_MySQL',
    'pass' => 'كلمة_المرور',
],
'app' => [
    'url' => 'https://YOUR-DOMAIN.com',
    'debug' => false,
],
```

**✅ أخبرني:** «تم الرفع» + اسم النطاق.

---

# المرحلة 5 — إعداد PHP و SSL

1. hPanel → **Advanced** → **PHP Configuration** → **PHP 8.1** أو **8.2**
2. hPanel → **Security** → **SSL** → فعّل الشهادة المجانية
3. فعّل **Force HTTPS**

---

# المرحلة 6 — إنشاء مسؤول النظام

1. افتح في المتصفح:
   ```
   https://YOUR-DOMAIN.com/setup.php
   ```
2. أدخل:
   - الاسم: مسؤول النظام
   - البريد: بريدك الرسمي (مثلاً `admin@yourdomain.com`)
   - كلمة مرور قوية
3. **احذف `setup.php`** من `public_html` فوراً!

---

# المرحلة 7 — التشغيل وإرسال الرابط

1. افتح: `https://YOUR-DOMAIN.com/login`
2. سجّل دخول كمسؤول
3. **الموظفون** → أضف مشرفين وموظفين
4. **أرسل للجميع:**

```
رابط النظام: https://YOUR-DOMAIN.com/login
البريد: (الذي أنشأته لهم)
كلمة المرور: (يُفضّل إرسالها بقناة منفصلة)
```

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| 500 Error | تحقق من `config.php` |
| 404 على /login | تأكد من `.htaccess` في public_html |
| Database connection failed | راجع اسم القاعدة والمستخدم وكلمة المرور |
| setup.php لا يفتح | تأكد أن الملف داخل public_html |

---

## تكلفة تقريبية

- Hostinger Premium: ~3–10 دولار/شهر (حسب المدة)
- نطاق مجاني السنة الأولى مع بعض الخطط
- SSL: مجاني

---

**ابدأ الآن بالمرحلة 1** ثم عد وقل: «انتهيت من المرحلة 1» مع اسم النطاق.
