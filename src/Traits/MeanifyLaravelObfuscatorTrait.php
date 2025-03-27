<?php

namespace Meanify\LaravelObfuscator\Traits;

use Meanify\LaravelObfuscator\Support\IdObfuscator;

trait MeanifyLaravelObfuscatorTrait
{
    protected bool $ignore_obfuscated_id_replacement = false;

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

    /**
     * @return $this
     */
    public function preserveRealId(): static
    {
        $this->ignore_obfuscated_id_replacement = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function replaceWithObfuscatedId(): static
    {
        $this->ignore_obfuscated_id_replacement = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasObfuscatedIdReplacementEnabled(): bool
    {
        return in_array('obfuscated_id', $this->appends ?? []) &&
            !$this->ignore_obfuscated_id_replacement;
    }

    /**
     * @return array
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if ($this->hasObfuscatedIdReplacementEnabled()) {
            $attributes['id'] = $this->obfuscated_id;
            unset($attributes['obfuscated_id']);
        }

        return $attributes;
    }
}
