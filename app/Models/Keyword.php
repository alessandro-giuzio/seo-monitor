<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'term',
        'target_url',
        'search_engine',
        'location',
        'device',
        'priority',
    ];

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return HasMany<RankingSnapshot, $this>
     */
    public function rankingSnapshots(): HasMany
    {
        return $this->hasMany(RankingSnapshot::class)->orderByDesc('checked_at');
    }

    /**
     * @return HasOne<RankingSnapshot, $this>
     */
    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(RankingSnapshot::class)->latestOfMany('checked_at');
    }
}
