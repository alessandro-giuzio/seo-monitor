<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'name',
        'domain',
        'notes',
    ];

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return HasMany<CompetitorKeywordSnapshot, $this>
     */
    public function keywordSnapshots(): HasMany
    {
        return $this->hasMany(CompetitorKeywordSnapshot::class)->orderByDesc('checked_at');
    }
}
