<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

final class CompanySetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'company_name', 'registration_number', 'tax_id',
        'address', 'currency', 'fiscal_year_start', 'logo_path', 'notification_channels',
    ];

    protected $casts = [
        'notification_channels' => 'array',
    ];
}
