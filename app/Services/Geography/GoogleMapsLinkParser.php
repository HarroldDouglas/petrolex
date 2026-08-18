<?php

namespace App\Services\Geography;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Extracts latitude/longitude from a pasted Google Maps link or raw coordinate pair.
 *
 * Admins copy a link from Google Maps rather than reading the two numbers out of it,
 * and a neighborhood saved without coordinates crashes the strongly-typed mobile apps.
 */
class GoogleMapsLinkParser
{
    /** Shortener hosts that must be resolved before the coordinates are visible. */
    private const SHORT_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co'];

    private const LAT_MIN = -90.0;
    private const LAT_MAX = 90.0;
    private const LNG_MIN = -180.0;
    private const LNG_MAX = 180.0;

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function parse(?string $input): ?array
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if ($this->isShortLink($input)) {
            $input = $this->expand($input) ?? $input;
        }

        foreach ($this->candidates($input) as $pair) {
            if ($this->isValid($pair[0], $pair[1])) {
                return [
                    'latitude' => round($pair[0], 8),
                    'longitude' => round($pair[1], 8),
                ];
            }
        }

        return null;
    }

    private function isShortLink(string $input): bool
    {
        $host = parse_url($input, PHP_URL_HOST);

        return $host !== null && in_array(strtolower($host), self::SHORT_HOSTS, true);
    }

    /** Resolves a shortened link to its full URL without following it in a browser. */
    private function expand(string $url): ?string
    {
        try {
            $response = Http::timeout(8)
                ->withoutRedirecting()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            $location = $response->header('Location');

            if ($location) {
                return $location;
            }

            /* Some shorteners answer 200 with a JS redirect instead of a Location header. */
            if (preg_match('#https://www\.google\.[^"\'<\s]+/maps[^"\'<\s]*#', $response->body(), $m)) {
                return html_entity_decode($m[0]);
            }
        } catch (\Throwable $e) {
            Log::warning('Google Maps short link expansion failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Coordinate candidates, most reliable first. `!3d<lat>!4d<lng>` is the resolved
     * place pin; `@lat,lng` is only the viewport center, so it ranks lower.
     *
     * @return list<array{0: float, 1: float}>
     */
    private function candidates(string $input): array
    {
        $found = [];

        if (preg_match('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $input, $m)) {
            $found[] = [(float) $m[1], (float) $m[2]];
        }

        foreach (['query', 'q', 'll', 'center', 'daddr', 'destination'] as $key) {
            if (preg_match('/[?&]'.$key.'=(-?\d+(?:\.\d+)?)[,%2C\s]+(-?\d+(?:\.\d+)?)/i', $input, $m)) {
                $found[] = [(float) $m[1], (float) $m[2]];
            }
        }

        if (preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $input, $m)) {
            $found[] = [(float) $m[1], (float) $m[2]];
        }

        /* Bare "4.0511, 9.7679" pasted straight out of the Maps sidebar. */
        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*[,;]\s*(-?\d+(?:\.\d+)?)\s*$/', $input, $m)) {
            $found[] = [(float) $m[1], (float) $m[2]];
        }

        return $found;
    }

    private function isValid(float $lat, float $lng): bool
    {
        if ($lat < self::LAT_MIN || $lat > self::LAT_MAX) {
            return false;
        }

        if ($lng < self::LNG_MIN || $lng > self::LNG_MAX) {
            return false;
        }

        /* (0,0) is the null-island artifact of a failed parse, never a real address. */
        return ! ($lat === 0.0 && $lng === 0.0);
    }
}
