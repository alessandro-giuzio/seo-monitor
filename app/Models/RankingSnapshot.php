<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class RankingSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword_id',
        'checked_at',
        'position',
        'search_volume',
        'difficulty',
        'serp_features',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'serp_features' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Keyword, $this>
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
