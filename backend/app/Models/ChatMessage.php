<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChatMessage extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'project_id', 'user_id', 'message', 'message_type', 'attachment_url', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
