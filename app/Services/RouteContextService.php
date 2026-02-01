<?php

declare(strict_types=1);

namespace App\Services;

class RouteContextService
{
    /**
     * Determine the route prefix based on the current context.
     *
     * Returns 'tenant' if running in a tenant context,
     * otherwise returns 'central' for central routes.
     */
    public static function getRoutePrefix(): string
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            return 'tenant';
        }

        return 'central';
    }

    /**
     * Check if the current request is in a tenant context.
     */
    public static function isTenantContext(): bool
    {
        return self::getRoutePrefix() === 'tenant';
    }

    /**
     * Check if the current request is in the central context.
     */
    public static function isCentralContext(): bool
    {
        return self::getRoutePrefix() === 'central';
    }
}
