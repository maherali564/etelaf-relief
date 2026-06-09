# ساهم (Sahem) — منصة التبرع والإغاثة الإنسانية المتكاملة

منصة متعددة اللغات (5 لغات) لجمع التبرعات لغزة مع لوحة تحكم إدارية كاملة (Filament). مبنية على Laravel 11 مع PHP 8.2+.

## المكدس التقني

- **الإطار:** Laravel 11
- **لوحة التحكم:** Filament 3.x
- **قاعدة البيانات:** SQLite (افتراضي) أو MySQL
- **المدفوعات:** Stripe، PayPal، Wise
- **اللغات:** العربية، الإنجليزية، الإسبانية، الإندونيسية، التركية

## المتطلبات

- PHP 8.2+
- Composer
- SQLite (افتراضي) أو MySQL
- PHP Extensions: intl, pdo, mbstring, openssl, tokenizer, json, ctype, filter, hash, session

## التثبيت السريع

```powershell
cd C:\Users\HP\Documents\etelaf-relief-laravel
.install.ps1
php artisan serve
```

افتح:
- الموقع: http://127.0.0.1:8000/ar
- لوحة التحكم: http://127.0.0.1:8000/admin

## بيانات الدخول الافتراضية

| البريد | كلمة المرور | الدور |
|--------|------------|-------|
| admin@sahem.org | password | super_admin |
| admin@etelafrelief.org | password | super_admin |

## اللغات المدعومة

| الرمز | اللغة | الاتجاه |
|-------|-------|---------|
| ar | العربية | RTL |
| en | English | LTR |
| es | Español | LTR |
| id | Bahasa Indonesia | LTR |
| tr | Türkçe | LTR |

## إرشادات المساهمة والتطوير

راجع [AGENTS.md](AGENTS.md) للحصول على إرشادات مفصلة حول الأمان، الأداء، جودة الكود، وسير العمل التنموي.

## الترخيص

حقوق النشر محفوظة لمشروع ساهم (Sahem).
