
<p align="center">
  <a href="https://www.meanify.co?from=github&lib=laravel-obfuscator">
    <img src="https://meanify.co/assets/core/img/logo/png/meanify_color_dark_horizontal_02.png" width="200" alt="Meanify Logo" />
  </a>
</p>

# Laravel Obfuscator

A secure and reversible numeric ID obfuscation package for Laravel. Ideal for hiding real primary keys in URLs and APIs while keeping the format clean, consistent, and safe.

> Now powered by [Hashids](https://hashids.org/php) (via `vinkla/hashids`) and fully integrated with Laravel.

---

## ✅ Features

- 🔐 Reversible obfuscation
- 🔢 Numeric-only or alphanumeric output (configurable)
- 📏 Fixed or minimum output length
- 🧂 Salted per model
- 📦 Simple integration with Eloquent
- 🔁 Artisan command to encode and decode values
- 🛠 Optional failure logging to database

---

## 🚀 Requirements

- Laravel ^10.0
- PHP ^8.0
- Package `vinkla/hashids` (already included)

---

## ⚙️ Installation

```bash
composer require meanify/laravel-obfuscator:dev-master
php artisan vendor:publish --tag=meanify-configs
```

If using DB logging for failures:

```bash
php artisan vendor:publish --tag=meanify-migrations
php artisan migrate
```

---

## 🧪 Configuration

`config/meanify-laravel-obfuscator.php`:

```php
return [
    'length' => 12, // minimum hash length
    'alphabetic' => false, // false = only numbers, true = alphanumeric
    'secret' => env('OBFUSCATOR_SECRET', env('APP_KEY')), // used as salt
    'log_to_db' => false, // store decode failures in the DB
];
```

---

## 🧬 How It Works

Internally uses `\Hashids\Hashids` with a salt based on your config + model name:

```php
$salt = config('meanify-laravel-obfuscator.secret') . '|' . $model;
```

This ensures uniqueness per model and environment.

---

## 🧑‍💻 Usage

```php
use Meanify\LaravelObfuscator\Support\IdObfuscator;

IdObfuscator::encode(123, App\Models\User::class); // e.g. '408248843449'

IdObfuscator::decode('408248843449', App\Models\User::class); // 123
```

---

## 🔁 Auto-Apply to Models

Create a trait like:

```php
use Meanify\LaravelObfuscator\Support\IdObfuscator;

public function getObfuscatedIdAttribute(): string
{
    return IdObfuscator::encode($this->id, static::class);
}
```

Optionally override `attributesToArray()` to replace `id` with `obfuscated_id` in JSON responses.

---

## 🧰 Artisan Command

```bash
php artisan meanify:obfuscator --encode --id=1,2,3 --model=App\Models\User
php artisan meanify:obfuscator --decode --id=408248843449 --model=App\Models\User
```

---

## ⚠️ Limitations

| Scenario                            | Supported? | Notes                                                                 |
|-------------------------------------|------------|-----------------------------------------------------------------------|
| Numeric obfuscation                | ✅         | Default mode (`alphabetic: false`)                                   |
| Alphanumeric obfuscation           | ✅         | Set `alphabetic: true` in config                                     |
| Obfuscation per model              | ✅         | Each model uses its class name in the salt                           |
| Fixed-length output                | ✅         | Length is minimum guaranteed by `hashids`                            |
| Reversibility with custom salt     | ✅         | Requires stable secret + model name                                  |
| Cryptographic security             | ❌         | Not intended for high-security encryption (only obfuscation)         |
| Collision resistance               | ✅         | Hashids guarantees uniqueness for the same salt and config           |
| Hidden ID in API responses         | ✅         | Replace `id` with `obfuscated_id` in `attributesToArray()`           |