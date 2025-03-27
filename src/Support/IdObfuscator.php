<?php
namespace Meanify\LaravelObfuscator\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Meanify\LaravelObfuscator\Models\ObfuscatorFailure;
use RuntimeException;

class IdObfuscator
{
    /**
     * @return int
     */
    protected static function getLength(): int
    {
        return config('meanify-laravel-obfuscator.length', 12);
    }

    /**
     * @param int $id
     * @param string $class_to_salt
     * @return string
     */
    public static function encode(int $id, string $class_to_salt): string
    {
        $class_parts = explode('\\', $class_to_salt);
        $salt        = array_pop($class_parts);

        $key = self::deriveKey($salt);
        $iv = str_repeat("\0", 16);

        $encrypted = openssl_encrypt((string)$id, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $int = gmp_init(bin2hex($encrypted), 16);
        $numeric = gmp_strval($int, 10);

        $length = self::getLength();
        $short  = substr(str_pad($numeric, $length - 1, '0', STR_PAD_LEFT), 0, $length - 1);
        return $short . self::generateCheckDigit($short);
    }

    /**
     * @param string $obfuscated
     * @param string $class_to_salt
     * @param bool $throwOnFailure
     * @return int|null
     */
    public static function decode(string $obfuscated, string $class_to_salt, bool $throwOnFailure = false): ?int
    {
        $length = self::getLength();

        $class_parts = explode('\\', $class_to_salt);
        $salt        = array_pop($class_parts);

        if (!ctype_digit($obfuscated) || strlen($obfuscated) !== $length) {
            return self::fail("Invalid format or length", $obfuscated, $salt, $throwOnFailure);
        }

        $short = substr($obfuscated, 0, -1);
        $checkDigit = substr($obfuscated, -1);

        if (self::generateCheckDigit($short) !== $checkDigit) {
            return self::fail("Check digit mismatch", $obfuscated, $salt, $throwOnFailure);
        }

        try {
            $key = self::deriveKey($salt);
            $iv = str_repeat("\0", 16);

            $padded = str_pad($short, 32, '0', STR_PAD_RIGHT);
            $hex = gmp_strval(gmp_init($padded, 10), 16);
            if (strlen($hex) % 2 !== 0) $hex = '0' . $hex;

            $bin = hex2bin(substr($hex, 0, 32));
            $decrypted = openssl_decrypt($bin, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

            return is_numeric($decrypted) ? (int)$decrypted : self::fail("Invalid decrypted value", $obfuscated, $salt, $throwOnFailure);
        } catch (\Throwable $e) {
            return self::fail("Exception: " . $e->getMessage(), $obfuscated, $salt, $throwOnFailure);
        }
    }

    /**
     * @param string $salt
     * @return string
     */
    protected static function deriveKey(string $salt): string
    {
        return substr(hash('sha256', config('app.key') . '|' . $salt), 0, 16);
    }

    /**
     * @param string $input
     * @return string
     */
    protected static function generateCheckDigit(string $input): string
    {
        $sum = 0;
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $digit = (int)$input[$len - 1 - $i];
            $sum += ($i % 2 === 0) ? $digit * 3 : $digit;
        }
        return (string)((10 - ($sum % 10)) % 10);
    }

    /**
     * @param string $reason
     * @param string $input
     * @param string $salt
     * @param bool $throw
     * @return int|null
     */
    protected static function fail(string $reason, string $input, string $salt, bool $throw): ?int
    {
        $modelName = Str::of(class_basename($salt))->snake()->lower();
        $context = ['reason' => $reason, 'input' => $input, 'model' => $modelName];

        Log::warning('[Laravel Obfuscator] Failed to decode', $context);

        if (Config::get('obfuscator.log_to_db')) {
            try {
                ObfuscatorFailure::create([
                    'model' => $modelName,
                    'input' => $input,
                    'reason' => $reason,
                    'context' => $context,
                ]);
            } catch (\Throwable $e) {
                Log::error('[Laravel Obfuscator] Failed to log to DB', [
                    'original_context' => $context,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        if ($throw) {
            throw new RuntimeException("Obfuscated ID decode failed: {$reason} (model: {$modelName})");
        }

        return null;
    }
}
