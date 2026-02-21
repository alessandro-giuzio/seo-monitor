<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SeoAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'type',
        'severity',
        'title',
        'message',
        'meta',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
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
