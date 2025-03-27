<?php
namespace Meanify\LaravelObfuscator\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Meanify\LaravelObfuscator\Models\ObfuscatorFailure;
use RuntimeException;
use Vinkla\Hashids\Facades\Hashids;

class IdObfuscator
{

    public static function encode(int &$id, string &$class_to_salt): string
    {
        $salt = self::buildSalt($class_to_salt);
        $length = config('meanify-laravel-obfuscator.length', 12);
        $alphabet = config('meanify-laravel-obfuscator.alphabetic', false)
            ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
            : '0123456789';

        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        return $hashids->encode($id);
    }

    public static function decode(string &$obfuscated, string &$class_to_salt, bool $throwOnFailure = false): ?int
    {
        $salt = self::buildSalt($class_to_salt);
        $length = config('meanify-laravel-obfuscator.length', 12);
        $alphabet = config('meanify-laravel-obfuscator.alphabetic', false)
            ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
            : '0123456789';

        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        $decoded = $hashids->decode($obfuscated);

        return isset($decoded[0]) && is_int($decoded[0])
            ? $decoded[0]
            : self::fail("Invalid obfuscated ID", $obfuscated, $salt, $throwOnFailure);
    }

    protected static function buildSalt(string $model): string
    {
        return config('meanify-laravel-obfuscator.secret') . '|' . $model;
    }
}
