<?php

namespace App\Support;

class PaddleBilling
{
    public static function verifySignature(string $payload, string $signature, ?string $secret): bool
    {
        if ($payload === '' || $signature === '' || ! is_string($secret) || $secret === '') {
            return false;
        }

        $parts = [];
        foreach (explode(';', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = $parts['ts'][0] ?? null;
        $signatures = $parts['h1'] ?? [];
        if (! $timestamp || $signatures === []) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.':'.$payload, $secret);

        foreach ($signatures as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public static function planForPrice(?string $priceId): ?string
    {
        if (! $priceId) {
            return null;
        }

        foreach (config('marketing.paddle.prices', []) as $plan => $configuredPriceId) {
            if ($configuredPriceId && hash_equals((string) $configuredPriceId, $priceId)) {
                return $plan;
            }
        }

        return null;
    }

    public static function priceForPlan(string $plan): ?string
    {
        $priceId = config('marketing.paddle.prices.'.$plan);

        return is_string($priceId) && $priceId !== '' ? $priceId : null;
    }
}
