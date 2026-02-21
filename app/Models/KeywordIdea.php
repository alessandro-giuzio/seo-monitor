<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class KeywordIdea extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'seed_keyword',
        'keyword',
        'search_volume',
        'keyword_difficulty',
        'cpc',
        'intent',
        'country',
    ];

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
