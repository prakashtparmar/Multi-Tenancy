<?php

namespace App\Services;

class RouteContextService
{
    /**
     * Determine the route prefix based on the current context.
     *
     * Returns 'tenant' if running in a tenant context,
     * otherwise returns 'central' for central routes.
     *
     * @return string
     */
    public static function getRoutePrefix(): string
    {
        // Primary check: if tenancy is initialized, we're in a tenant context
        if (tenancy()->initialized) {
            return 'tenant';
        }

        return 'central';
    }

    /**
     * Check if the current request is in a tenant context.
     *
     * @return bool
     */
    public static function isTenantContext(): bool
    {
        return self::getRoutePrefix() === 'tenant';
    }

    /**
     * Check if the current request is in the central context.
     *
     * @return bool
     */
    public static function isCentralContext(): bool
    {
        return self::getRoutePrefix() === 'central';
    }
}
