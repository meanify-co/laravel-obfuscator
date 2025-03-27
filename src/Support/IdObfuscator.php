<?php

namespace Meanify\LaravelObfuscator\Support;

class IdObfuscator
{

    public static function encode(int $id, string $class_to_salt): string
    {
        $salt     = self::buildSalt($class_to_salt);
        $length   = config('meanify-laravel-obfuscator.length', 12);
        $alphabet = config('meanify-laravel-obfuscator.alphabetic', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        return $hashids->encode($id);
    }

    public static function decode(string $obfuscated, string $class_to_salt, bool $throwOnFailure = false): ?int
    {
        $salt     = self::buildSalt($class_to_salt);
        $length   = config('meanify-laravel-obfuscator.length', 12);
        $alphabet = config('meanify-laravel-obfuscator.alphabetic', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

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
