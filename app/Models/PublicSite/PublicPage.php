<?php

namespace App\Models\PublicSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'page_type', 'slug', 'url_path', 'title', 'headline', 'excerpt', 'content_html', 'sections',
        'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'og_image_path',
        'robots_index', 'robots_follow', 'sitemap_include', 'sitemap_priority', 'sitemap_changefreq',
        'schema_json', 'is_published', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'sections' => 'array',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'sitemap_include' => 'boolean',
        'sitemap_priority' => 'decimal:1',
        'schema_json' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
