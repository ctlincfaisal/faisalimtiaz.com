<?php

namespace App\Support;

final class MarketingTracking
{
    public static function url(string $routeName, array $parameters): string
    {
        $trackingBaseUrl = trim((string) config('marketing.tracking_base_url'));

        if ($trackingBaseUrl === '') {
            return route($routeName, $parameters, true);
        }

        return rtrim($trackingBaseUrl, '/').'/'.ltrim(route($routeName, $parameters, false), '/');
    }
}
