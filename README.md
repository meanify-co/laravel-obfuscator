<p align="center">
  <a href="https://www.meanify.co?from=github&lib=laravel-obfuscator">
    <img src="https://meanify.co/assets/core/img/logo/png/meanify_color_dark_horizontal_02.png" width="200" alt="Meanify Logo" />
  </a>
</p>

# Laravel Obfuscator

A secure, reversible, numeric ID obfuscation package for Laravel.  
Ideal for hiding real primary keys in URLs and APIs while keeping the format clean and short.

---

## ✅ Features

- Numeric-only obfuscated IDs
- Reversible (encode/decode)
- Fixed-length configurable output (e.g., 10–14 digits)
- Built-in check digit for integrity validation
- Salted by model class name
- Fallback logging (file and/or database)
- Artisan command to list and clear failures
- `trait` for automatic obfuscation in models
- Optional replacement of `id` in JSON/array output

---

## ⚙️ Requirements

- Laravel ^10.0 (tested on 10–12)
- PHP ^8.0
- PHP GMP extension

---

## 🚀 Installation

```bash
composer require meanify/laravel-obfuscator
php artisan vendor:publish --tag=meanify-configs
php artisan vendor:publish --tag=meanify-migrations
php artisan migrate
```

---

## 🧬 Usage in Model (with Trait)

```php
use Meanify\LaravelObfuscator\Traits\MeanifyLaravelObfuscatorTrait;

class User extends Model
{
    use MeanifyLaravelObfuscatorTrait;

    protected $appends = ['obfuscated_id'];
}
```

When `obfuscated_id` is in `appends`, it will automatically:
- Encode the real `id`
- Replace the `id` field in the output (JSON/array)
- Hide the `obfuscated_id` itself (not shown in response)

---

## ✅ Helper Methods (Fluent API)

```php
$user->preserveRealId(); // disables ID replacement temporarily
$user->replaceWithObfuscatedId(); // re-enables replacement
$user->hasObfuscatedIdReplacementEnabled(); // checks if replacement is active
```

---

## ⚠️ Obfuscation Scope & Collision Risk

This package uses the model's class name as the obfuscation salt by default.

**You are safe by default if each model represents a single real table.**

| Scenario                                                       | Risk of Collision? |
|----------------------------------------------------------------|--------------------|
| One model → one physical table                                 | ❌ No              |
| Model pointing to multiple tables (`$table` changes dynamically) | ✅ Yes             |
| Same model used across multiple apps/databases                 | ✅ Yes             |
| Duplicated IDs across environments (e.g., staging/prod)        | ✅ Yes             |

> To ensure unique obfuscation context across environments or apps, consider using a custom salt by defining `public static string $obfuscator_salt` in your model.

---

## 📦 Artisan Command

List or clear failed decodings:

```bash
php artisan obfuscator:failures
php artisan obfuscator:failures --clear
```

---

## 💡 Example Output

```json
{
  "id": "0283917045",
  "name": "John",
  "email": "john@example.com"
}
```

---

## 🛡️ License

MIT © Meanify