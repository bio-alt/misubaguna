<?php

namespace App\Models\PublicSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug', 'url_path', 'name', 'summary', 'description_html', 'parent_group', 'sort_order',
        'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'og_image_path',
        'robots_index', 'robots_follow', 'sitemap_include', 'sitemap_priority', 'sitemap_changefreq',
        'schema_json', 'is_published',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'sitemap_include' => 'boolean',
        'sitemap_priority' => 'decimal:1',
        'schema_json' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * Published products that belong to this category.
     */
    public function productsQuery()
    {
        return PublicProduct::where('is_published', true)
            ->where(function ($query) {
                $query->where('product_group', $this->name)
                    ->orWhere('product_group', $this->parent_group)
                    ->orWhere('product_subgroup', $this->name);
            });
    }

    /**
     * Published categories that actually list at least one product.
     */
    public static function publishedWithProducts()
    {
        return static::where('is_published', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (self $category) => $category->productsQuery()->exists())
            ->values();
    }
}
