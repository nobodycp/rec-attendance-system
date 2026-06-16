# نظام حضور ومهام — جمعية مركز الإرشاد التربوي REC

نظام إنتاجي لإدارة حضور الموظفين والمهام اليومية والتقييم الشهري.

يعمل كحاوية Docker ويُنشر على [Coolify](https://coolify.io) مع MySQL.

## المتطلبات

- Coolify على VPS
- مستودع Git
- قاعدة بيانات MySQL 8 (خدمة منفصلة في Coolify)

## النشر على Coolify

راجع **COOLIFY-DEPLOY-AR.md** — دليل خطوة بخطوة.

### ملخص سريع

1. أنشئ MySQL في Coolify واحفظ `DATABASE_URL`
2. أنشئ Application من Dockerfile
3. عيّن متغيرات البيئة (`APP_URL`, `SETUP_ENABLED=true`, ...)
4. Deploy ثم افتح `/setup.php` لإنشاء مسؤول النظام
5. عطّل `SETUP_ENABLED=false` وأعد النشر

## متغيرات البيئة

| المتغير | الوصف |
|---------|--------|
| `DATABASE_URL` | رابط MySQL من Coolify — `mysql://user:pass@host:3306/db` |
| `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS` | بديل عند عدم وجود `DATABASE_URL` |
| `APP_URL` | رابط الموقع (`https://...`) |
| `APP_DEBUG` | `false` في الإنتاج |
| `SETUP_ENABLED` | `true` مؤقتاً لإنشاء المسؤول الأول |

راجع `.env.example` للقائمة الكاملة.

## الهيكل

```
public/     ← جذر الويب
config/     ← الإعدادات (من متغيرات البيئة)
src/        ← منطق التطبيق
views/      ← القوالب
database/   ← schema.sql + أدوات التثبيت
docker/     ← إعدادات الحاوية
```
