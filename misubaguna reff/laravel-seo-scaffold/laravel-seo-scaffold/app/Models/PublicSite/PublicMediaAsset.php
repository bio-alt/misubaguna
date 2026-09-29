<?php

namespace App\Models\PublicSite;

use Illuminate\Database\Eloquent\Model;

class PublicMediaAsset extends Model
{
    protected $fillable = [
        'path', 'original_filename', 'mime_type', 'title', 'alt_text', 'width', 'height', 'size_bytes', 'metadata', 'is_public',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];
}
