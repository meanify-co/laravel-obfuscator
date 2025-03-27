<?php

namespace Meanify\LaravelObfuscator\Traits;

use Meanify\LaravelObfuscator\Support\IdObfuscator;

trait MeanifyLaravelObfuscatorTrait
{
    /**
     * @return string
     */
    public function getObfuscatedIdAttribute(): string
    {
        return IdObfuscator::encode($this->id, static::class);
    }

    /**
     * @param string $obfuscated
     * @param bool $throwOnFailure
     * @return int|null
     */
    public static function decodeObfuscatedId(string $obfuscated, bool $throwOnFailure = false): ?int
    {
        return IdObfuscator::decode($obfuscated, static::class, $throwOnFailure);
    }
}