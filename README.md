<p align="center">
  <a href="https://www.meanify.co?from=github&lib=laravel-permissions">
    <img src="https://meanify.co/assets/core/img/logo/png/meanify_color_dark_horizontal_02.png" width="200" alt="Meanify Logo" />
  </a>
</p>

# Laravel Obfuscator


A secure, reversible, numeric ID obfuscation package for Laravel. Ideal for hiding real primary keys in URLs and APIs while keeping the format clean and short.

## Requirements

- Laravel ^10.0
- PHP ^8.0
- PHP GMP

## Features

- Numeric-only obfuscated IDs
- Reversible (encode/decode)
- Fixed-length configurable output (e.g., 10 digits)
- Built-in check digit (integrity validation)
- Salted by model class name
- Fallback logging (file and/or database)
- Artisan command to list and clear failures

## Installation

```bash
composer require meanify/laravel-obfuscator:dev-master
php artisan vendor:publish --tag=meanify-configs
php artisan vendor:publish --tag=meanify-migrations
php artisan migrate