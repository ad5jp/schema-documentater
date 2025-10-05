<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

class Config
{
    private static $configuration = [];

    public static function set(array $args): void
    {
        foreach ($args as $i => $arg) {
            if (strpos($arg, '--') === 0) {
                // ロングオプションを解析
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = isset($parts[1]) ? $parts[1] : true; // 値がなければtrueとして扱う
                self::$configuration[$key] = $value;
            } elseif (strpos($arg, '-') === 0 && strlen($arg) > 1) {
                // ショートオプションを解析
                $key = substr($arg, 1);
                // 次の要素がオプションでなければ、それを値として扱う
                if (isset($argv[$i + 1]) && strpos($argv[$i + 1], '-') !== 0) {
                    self::$configuration[$key] = $argv[$i + 1];
                } else {
                    self::$configuration[$key] = true;
                }
            }
        }
    }

    public static function getValue(string $key, $default = null): ?string
    {
        if (!isset(self::$configuration[$key])) {
            return $default;
        }

        if (is_bool(self::$configuration[$key])) {
            return $default;
        }

        if (self::$configuration[$key] === "") {
            return $default;
        }

        return (string)self::$configuration[$key];
    }

    public static function getValueAsBool(string $key): bool
    {
        if (!isset(self::$configuration[$key])) {
            return false;
        }

        if (self::$configuration[$key] === "false") {
            return false;
        }

        return true;
    }
}
