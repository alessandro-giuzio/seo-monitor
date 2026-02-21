<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DomainMetricsSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'snapshot_at',
        'estimated_traffic',
        'organic_keywords',
        'referring_domains',
        'backlinks_count',
        'visibility_index',
        'avg_position',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_at' => 'datetime',
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
