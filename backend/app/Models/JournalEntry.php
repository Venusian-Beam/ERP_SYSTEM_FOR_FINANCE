<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class JournalEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'reference', 'posted_at', 'description', 'status', 'created_by'];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->with('account');
    }
}
