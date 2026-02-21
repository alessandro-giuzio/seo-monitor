<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SeoAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'url',
        'audited_at',
        'status',
        'score',
        'title',
        'meta_description',
        'canonical',
        'h1_count',
        'image_without_alt',
        'internal_links',
        'external_links',
        'word_count',
        'issues',
    ];

    protected function casts(): array
    {
        return [
            'audited_at' => 'datetime',
            'issues' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
