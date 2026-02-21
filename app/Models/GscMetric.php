<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class GscMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'metric_date',
        'query',
        'page_url',
        'clicks',
        'impressions',
        'ctr',
        'avg_position',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'ctr' => 'decimal:4',
            'avg_position' => 'decimal:2',
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
