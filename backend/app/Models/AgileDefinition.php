<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

final class AgileDefinition extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'definition_type', 'content', 'updated_by'];

    protected $casts = [
        'content' => 'json',
    ];
}
