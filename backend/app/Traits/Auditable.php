<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(static function (Model $model): void {
            self::recordAuditLog('created', $model, [], $model->getAttributes());
        });

        static::updated(static function (Model $model): void {
            self::recordAuditLog('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(static function (Model $model): void {
            self::recordAuditLog('deleted', $model, $model->getOriginal(), []);
        });
    }

    private static function recordAuditLog(string $action, Model $model, array $old, array $new): void
    {
        // Strip sensitive fields from log
        $sensitive = ['password', 'remember_token', 'api_token'];
        $old = array_diff_key($old, array_flip($sensitive));
        $new = array_diff_key($new, array_flip($sensitive));

        AuditLog::create([
            'tenant_id'      => $model->tenant_id ?? null,
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
