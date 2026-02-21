<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UptimeCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'checked_at',
        'status_code',
        'response_time_ms',
        'is_up',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'is_up' => 'boolean',
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
