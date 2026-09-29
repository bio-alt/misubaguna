<?php

namespace App\Models\PublicSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicService extends Model
{
    use SoftDeletes;

    protected $table = 'public_services';

    protected $fillable = [
        'slug',
        'url_path',
        'name',
        'service_group',
        'headline',
        'summary',
        'content_html',
        'key_features',
        'typical_problems',
        'scope_of_work',
        'industries_served',
        'materials_equipment',
        'related_products',
        'icon_svg',
        'sort_order',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_path',
        'robots_index',
        'robots_follow',
        'sitemap_include',
        'sitemap_priority',
        'sitemap_changefreq',
        'schema_json',
        'is_published',
    ];

    protected $casts = [
        'key_features' => 'array',
        'typical_problems' => 'array',
        'scope_of_work' => 'array',
        'industries_served' => 'array',
        'materials_equipment' => 'array',
        'related_products' => 'array',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'sitemap_include' => 'boolean',
        'sitemap_priority' => 'decimal:1',
        'schema_json' => 'array',
        'is_published' => 'boolean',
    ];
}
