<?php

namespace App\Models\PublicSite;

use Illuminate\Database\Eloquent\Model;

class PublicRedirect extends Model
{
    protected $fillable = [
        'source_path', 'target_url', 'status_code', 'is_active', 'note', 'last_hit_at', 'hit_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_hit_at' => 'datetime',
    ];
}
