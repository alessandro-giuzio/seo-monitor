<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ReleaseQaRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'checked_at',
        'environment',
        'release_tag',
        'status',
        'score',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'summary' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return HasMany<ReleaseQaIssue, $this>
     */
    public function issues(): HasMany
    {
        return $this->hasMany(ReleaseQaIssue::class)->orderByDesc('severity');
    }
}
