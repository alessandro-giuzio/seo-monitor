<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Backlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'source_url',
        'target_url',
        'anchor_text',
        'source_authority',
        'is_nofollow',
        'is_toxic',
        'found_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_nofollow' => 'boolean',
            'is_toxic' => 'boolean',
            'found_at' => 'datetime',
            'last_seen_at' => 'datetime',
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
