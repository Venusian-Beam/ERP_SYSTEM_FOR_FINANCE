<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'status',
    ];
}
