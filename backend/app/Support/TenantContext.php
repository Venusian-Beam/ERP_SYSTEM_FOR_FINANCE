<?php

declare(strict_types=1);

namespace App\Support;

final class TenantContext
{
    private static ?int $tenantId = null;

    public static function set(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    public static function id(): ?int
    {
        return self::$tenantId;
    }

    public static function requireId(): int
    {
        if (self::$tenantId === null) {
            abort(403, 'Tenant context has not been resolved.');
        }

        return self::$tenantId;
    }

    public static function clear(): void
    {
        self::$tenantId = null;
    }
}
