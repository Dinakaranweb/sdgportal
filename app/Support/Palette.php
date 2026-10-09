<?php

namespace App\Support;

/**
 * Small colour utility used to derive the shades, tints and overlay values
 * that the stylesheet needs from a single colour stored in the database.
 */
class Palette
{
    /**
     * Mix a hex colour towards white (positive amount) or black (negative).
     *
     * @param  int  $amount  percentage between -100 and 100
     */
    public static function shade(string $hex, int $amount): string
    {
        [$r, $g, $b] = static::toRgb($hex);

        if ($amount >= 0) {
            $ratio = min(100, $amount) / 100;
            $r = $r + (255 - $r) * $ratio;
            $g = $g + (255 - $g) * $ratio;
            $b = $b + (255 - $b) * $ratio;
        } else {
            $ratio = 1 - (min(100, abs($amount)) / 100);
            $r *= $ratio;
            $g *= $ratio;
            $b *= $ratio;
        }

        return sprintf('#%02x%02x%02x', (int) round($r), (int) round($g), (int) round($b));
    }

    /**
     * Return the colour as an rgba() string at the given opacity.
     */
    public static function alpha(string $hex, float $opacity): string
    {
        [$r, $g, $b] = static::toRgb($hex);

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, round(max(0, min(1, $opacity)), 3));
    }

    /**
     * Space separated channels, handy for `rgb(var(--token) / 40%)` usage.
     */
    public static function channels(string $hex): string
    {
        return implode(' ', static::toRgb($hex));
    }

    /**
     * Pick black or white text so copy stays legible on the given background.
     */
    public static function readableInk(string $hex, string $dark = '#13201c', string $light = '#ffffff'): string
    {
        return static::luminance($hex) > 0.58 ? $dark : $light;
    }

    public static function isLight(string $hex): bool
    {
        return static::luminance($hex) > 0.58;
    }

    public static function luminance(string $hex): float
    {
        [$r, $g, $b] = static::toRgb($hex);

        $channel = function (float $value): float {
            $value /= 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function toRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '1f6feb';
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function normalise(?string $hex, string $fallback = '#1f6feb'): string
    {
        if (blank($hex)) {
            return $fallback;
        }

        $value = '#'.ltrim(trim($hex), '#');

        return preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) ? strtolower($value) : $fallback;
    }
}
