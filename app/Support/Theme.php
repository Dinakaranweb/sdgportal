<?php

namespace App\Support;

use App\Models\Goal;
use App\Models\Setting;

/**
 * Turns the stored theme settings into the CSS custom properties that the
 * stylesheet reads. Everything the layout paints — brand colours, surfaces,
 * typography, radius, container width — comes from here, so the look of the
 * portal can be changed from the control panel without touching any CSS.
 */
class Theme
{
    /**
     * Defaults used when a setting has not been saved yet.
     */
    public static function defaults(): array
    {
        return [
            'theme_primary' => '#0a6b4f',
            'theme_secondary' => '#1b4f8a',
            'theme_accent' => '#e3a008',
            'theme_page_bg' => '#f5f7f6',
            'theme_surface' => '#ffffff',
            'theme_text' => '#17241f',
            'theme_muted' => '#5d6b66',
            'theme_border' => '#e2e8e5',
            'theme_header_bg' => '#ffffff',
            'theme_header_text' => '#17241f',
            'theme_utility_bg' => '#0a6b4f',
            'theme_utility_text' => '#ffffff',
            'theme_footer_bg' => '#10231d',
            'theme_footer_text' => '#d7e2dd',
            'theme_heading_font' => 'Poppins',
            'theme_body_font' => 'Inter',
            'theme_radius' => '14',
            'theme_container' => '1240',
            'theme_heading_weight' => '700',
            'theme_tile_shape' => 'rounded',
            'theme_banner_style' => 'gradient',
            'theme_hero_overlay' => '0.55',
        ];
    }

    public static function setting(string $key): string
    {
        $value = Setting::value($key);

        return filled($value) ? (string) $value : (string) (static::defaults()[$key] ?? '');
    }

    /**
     * Google Fonts stylesheet URL for the two configured families.
     */
    public static function fontUrl(): ?string
    {
        $families = collect([static::setting('theme_heading_font'), static::setting('theme_body_font')])
            ->filter()
            ->map(fn ($f) => trim($f))
            ->unique()
            ->reject(fn ($f) => in_array(strtolower($f), ['system', 'system-ui', 'inherit', ''], true));

        if ($families->isEmpty()) {
            return null;
        }

        $query = $families
            ->map(fn ($f) => 'family='.str_replace(' ', '+', $f).':wght@300;400;500;600;700;800')
            ->implode('&');

        return 'https://fonts.googleapis.com/css2?'.$query.'&display=swap';
    }

    public static function fontStack(string $key): string
    {
        $font = trim(static::setting($key));
        $fallback = "system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif";

        return filled($font) ? "'".$font."', ".$fallback : $fallback;
    }

    /**
     * Site wide custom properties.
     *
     * @return array<string, string>
     */
    public static function tokens(): array
    {
        $primary = Palette::normalise(static::setting('theme_primary'), '#0a6b4f');
        $secondary = Palette::normalise(static::setting('theme_secondary'), '#1b4f8a');
        $accent = Palette::normalise(static::setting('theme_accent'), '#e3a008');

        return [
            '--brand' => $primary,
            '--brand-dark' => Palette::shade($primary, -24),
            '--brand-light' => Palette::shade($primary, 82),
            '--brand-soft' => Palette::alpha($primary, 0.1),
            '--brand-ring' => Palette::alpha($primary, 0.3),
            '--brand-ink' => Palette::readableInk($primary),
            '--secondary' => $secondary,
            '--secondary-dark' => Palette::shade($secondary, -22),
            '--secondary-soft' => Palette::alpha($secondary, 0.1),
            '--accent' => $accent,
            '--accent-dark' => Palette::shade($accent, -20),
            '--accent-soft' => Palette::alpha($accent, 0.14),
            '--accent-ink' => Palette::readableInk($accent),
            '--page-bg' => Palette::normalise(static::setting('theme_page_bg'), '#f5f7f6'),
            '--surface' => Palette::normalise(static::setting('theme_surface'), '#ffffff'),
            '--surface-alt' => Palette::shade(Palette::normalise(static::setting('theme_page_bg'), '#f5f7f6'), -4),
            '--ink' => Palette::normalise(static::setting('theme_text'), '#17241f'),
            '--muted' => Palette::normalise(static::setting('theme_muted'), '#5d6b66'),
            '--line' => Palette::normalise(static::setting('theme_border'), '#e2e8e5'),
            '--header-bg' => Palette::normalise(static::setting('theme_header_bg'), '#ffffff'),
            '--header-ink' => Palette::normalise(static::setting('theme_header_text'), '#17241f'),
            '--utility-bg' => Palette::normalise(static::setting('theme_utility_bg'), $primary),
            '--utility-ink' => Palette::normalise(static::setting('theme_utility_text'), '#ffffff'),
            '--footer-bg' => Palette::normalise(static::setting('theme_footer_bg'), '#10231d'),
            '--footer-ink' => Palette::normalise(static::setting('theme_footer_text'), '#d7e2dd'),
            '--font-heading' => static::fontStack('theme_heading_font'),
            '--font-body' => static::fontStack('theme_body_font'),
            '--heading-weight' => static::setting('theme_heading_weight') ?: '700',
            '--radius' => static::numeric('theme_radius', 14).'px',
            '--radius-sm' => max(2, (int) round(static::numeric('theme_radius', 14) * 0.5)).'px',
            '--radius-lg' => (static::numeric('theme_radius', 14) + 8).'px',
            '--container' => static::numeric('theme_container', 1240).'px',
            '--hero-overlay' => (string) static::decimal('theme_hero_overlay', 0.55),
            // The accent is reused as the default goal colour until a goal
            // overrides it on its own pages.
            '--goal' => $primary,
            '--goal-dark' => Palette::shade($primary, -24),
            '--goal-light' => Palette::shade($primary, 82),
            '--goal-soft' => Palette::alpha($primary, 0.1),
            '--goal-ring' => Palette::alpha($primary, 0.3),
            '--goal-ink' => Palette::readableInk($primary),
        ];
    }

    /**
     * Per goal overrides, layered on top of the site tokens.
     *
     * @return array<string, string>
     */
    public static function goalTokens(?Goal $goal): array
    {
        if (! $goal) {
            return [];
        }

        $tokens = [];

        foreach ($goal->paletteTokens() as $name => $value) {
            $tokens['--'.$name] = $value;
        }

        foreach ((array) $goal->theme as $name => $value) {
            if (blank($value)) {
                continue;
            }

            $tokens['--'.ltrim((string) $name, '-')] = (string) $value;
        }

        return $tokens;
    }

    /**
     * Render tokens as an inline style attribute value.
     */
    public static function inline(array $tokens): string
    {
        return collect($tokens)
            ->map(fn ($value, $name) => $name.':'.$value)
            ->implode(';');
    }

    public static function styleBlock(?Goal $goal = null): string
    {
        $tokens = array_merge(static::tokens(), static::goalTokens($goal));

        $lines = collect($tokens)->map(fn ($value, $name) => '  '.$name.': '.$value.';')->implode("\n");

        return ":root {\n".$lines."\n}";
    }

    public static function tileShape(): string
    {
        return static::setting('theme_tile_shape') ?: 'rounded';
    }

    public static function bannerStyle(): string
    {
        return static::setting('theme_banner_style') ?: 'gradient';
    }

    protected static function numeric(string $key, int $default): int
    {
        $value = (int) preg_replace('/[^0-9]/', '', static::setting($key));

        return $value > 0 ? $value : $default;
    }

    protected static function decimal(string $key, float $default): float
    {
        $value = static::setting($key);

        return is_numeric($value) ? (float) $value : $default;
    }
}
